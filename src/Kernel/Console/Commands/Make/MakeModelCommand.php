<?php

namespace FW\Kernel\Console\Commands\Make;

use FW\Kernel\Console\Input;
use FW\Kernel\Console\TextBuilder;
use FW\Kernel\Console\Output\Output;
use FW\Kernel\Database\ORM\Relation\Relation;

class MakeModelCommand extends MakeCommand
{
    protected string $namespace;
    protected array $uses = [];
    protected array $columns = [];

    public function __construct()
    {
        $this->namespace = ltrim(config('app.models.namespace'), '\\');
    }

    public function getName(): string
    {
        return 'make:model';
    }

    public function getDescription(): string
    {
        return 'Create new Model class.';
    }

    protected function getBaseDir(): string
    {
        return config('app.models.dir');
    }

    public function getRequiredParameters(): array
    {
        return [
            'name' => 'Name of Model class.',
        ];
    }

    protected function getStubFile(): string
    {
        return __DIR__ . '/stubs/Model.stub';
    }

    public function make(Input $input, Output $output): void
    {
        $name = $this->getParameters($input)['name'];

        [$className, $this->namespace] = $this->normalizeClassAndNamespace($name, $this->namespace);

        $relations = $this->renderRelations($output);
        $columns = $this->renderColumns($output);

        $this->stubReplacements = [
            'className' => $className,
            'namespace' => $this->namespace,
            'relations' => $relations,
            'columns' => $columns,
            'use' => $this->renderUses(),
        ];

        $this->fileName = "$name.php";

        $this->successful = 'New Model class has been successfully created.';
    }

    protected function renderUses(): string
    {
        return TextBuilder::getBuilder()
            ->foreach($this->uses, function ($key, $use) {
                return "use $use;\n";
            });
    }

    protected function addUse(string $class): void
    {
        if (in_array($class, $this->uses)) {
            return;
        }

        $className = ltrim($class, $this->namespace);

        if (count(explode('\\', $className)) > 1) {
            $this->uses[] = $class;
        }
    }

    protected function renderColumns(Output $output): string
    {
        $this->buildColumns($output);

        if (!empty($this->columns)) {
            $definition = TextBuilder::getBuilder()
                ->nextLine()
                ->tab()->writeln('public static function getColumns(): array')
                ->writeln('{')
                ->tab()->writeln('return [')
                ->tab()->foreach($this->columns, function ($column) {
                        return "'$column',\n";
                    })
                ->dropTab()->writeln("];")
                ->dropTab()->writeln('}');
        }

        return $definition ?? '';
    }

    protected function buildColumns(Output $output): void
    {
        if (!$output->confirm('Define columns array (it will optimize queries)?')) {
            $this->columns = [];

            return;
        }

        $columns = empty($this->columns) ? '' : '[' . implode(', ', $this->columns) . ']';

        while (($name = $output->input("Column name $columns (Empty to stop): ")) !== '') {
            if (!in_array($name, $this->columns)) {
                $this->columns[] = $name;
            }

            $columns = '[' . implode(', ', $this->columns) . ']';
        }
    }

    protected function renderRelations(Output $output): string
    {
        $relations = $this->buildRelation($output);

        if (!empty($relations)) {
            $definition = "\n\tpublic const RELATIONS = [\n";
        }

        foreach ($relations as $relation) {
            $name = $relation['name'];
            unset($relation['name']);

            $definition .= TextBuilder::getBuilder()
                ->tab(2)
                    ->writeln("'$name' => [")
                    ->tab()->foreach($relation, function ($key, $value) {
                            if (!in_array($key, ['type', 'class'])) {
                                $value = "'$value'";
                            }

                            return "'$key' => $value,\n";
                        })
                    ->dropTab()->writeln("],");
        }

        return isset($definition) ? $definition .= "\t];\n" : '';
    }

    protected function buildRelation(Output $output): array
    {
        $message = 'Do you want to set relations?';
        $relations = [];

        while ($output->confirm($message)) {
            $relation = [];
            $relation['name'] = $output->input('Insert relation name: ');
            $type = $output->choose('Choose relation type', Relation::TYPES);
            $related = "$this->namespace\\" . $output->input("Insert related class $this->namespace\\");
            $this->addUse($related);
            $this->addUse(Relation::class);

            $className = explode('\\', $related);
            $relation['class'] = array_pop($className) . '::class';

            switch ($type) {
                case Relation::TO_ONE:
                    $relation['field'] = $field = $output->input("Insert field name that represents $related in DB: ");
                    $relation['type'] = 'Relation::TO_ONE';
                    $this->columns[] = $field;

                    break;
                case Relation::ONE_TO_MANY:
                    $relation['field'] = $output->input("Insert field name in $related that represents this object in DB: ");
                    $relation['type'] = 'Relation::ONE_TO_MANY';

                    break;
                case Relation::MANY_TO_MANY:
                    $relation['field'] = $output->input("Insert field name in pivot table that represents $related in DB: ");
                    $relation['defined_by'] = $output->input("Insert field name in pivot table that represents this object in DB: ");
                    $relation['type'] = 'Relation::MANY_TO_MANY';

                    $pivot = $output->input('Name of pivot table (empty for default): ');

                    if ($pivot !== '') {
                        $relation['pivot'] = $pivot;
                    }

                    break;
            }

            if (($inversedBy = $output->input('Set inversed field [Enter to skip]: ')) !== '') {
                $relation['inversed_by'] = $inversedBy;
            }

            $relations[] = $relation;
            $message = 'Set one more relation?';
        }

        return $relations;
    }
}
