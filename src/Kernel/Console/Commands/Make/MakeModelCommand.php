<?php

namespace Fwt\Framework\Kernel\Console\Commands\Make;

use Fwt\Framework\Kernel\App;
use Fwt\Framework\Kernel\Console\Input;
use Fwt\Framework\Kernel\Console\Output\MessageBuilder;
use Fwt\Framework\Kernel\Console\Output\Output;
use Fwt\Framework\Kernel\Database\ORM\Relation\AbstractRelation;

class MakeModelCommand extends AbstractMakeCommand
{
    protected string $namespace;
    protected array $uses = [];
    protected array $columns = [];

    public function __construct()
    {
        $this->namespace = ltrim(App::$app->getConfig('app.models.namespace'), '\\');
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
        return App::$app->getConfig('app.models.dir');
    }

    public function getParameters(): array
    {
        return [
            'name' => ['Name of Model class.'],
        ];
    }

    protected function getStubFile(): string
    {
        return __DIR__ . '/stubs/Model.stub';
    }

    public function execute(Input $input, Output $output): void
    {
        //todo: add recursive dir creation
        if (!empty($params = $input->getParameters())) {
            $name = $params[0];
        } else {
            $name = $output->input('Input Model name: ');
        }

        $relations = $this->renderRelations($output);
        $columns = $this->renderColumns($output);

        $stub = $this->replaceStubTemplates([
            'class_name' => $name,
            'namespace' => $this->namespace,
            'relations' => $relations,
            'columns' => $columns,
            'use' => $this->renderUses(),
        ]);

        if ($this->createFile("$name.php", $stub)) {
            $output->success('New Model is created successfully!');
        } else {
            $output->error('Something went wrong.');
        }
    }

    protected function renderUses(): string
    {
        return MessageBuilder::getBuilder()
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
            $definition = MessageBuilder::getBuilder()
                ->nextLine()
                ->tab()->writeln('protected static array $columns = [')
                ->tab()->foreach($this->columns, function ($key, $column) {
                        return "'$column',\n";
                    })
                ->dropTab()->writeln("];");
        }

        return $definition ?? '';
    }

    protected function buildColumns(Output $output): void
    {
        if (!$output->confirm('Define columns array?')) {
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
            $definition = "\n\tprotected const RELATIONS = [\n";
        }

        foreach ($relations as $relation) {
            $name = $relation['name'];
            unset($relation['name']);

            $definition .= MessageBuilder::getBuilder()
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
            $type = $output->choose('Choose relation type', AbstractRelation::TYPES);
            $related = "$this->namespace\\" . $output->input("Insert related class $this->namespace\\");
            $this->addUse($related);
            $this->addUse(AbstractRelation::class);

            $className = explode('\\', $related);
            $relation['class'] = array_pop($className) . '::class';

            switch ($type) {
                case AbstractRelation::TO_ONE:
                    $relation['field'] = $field = $output->input("Insert field name that represents $related in DB: ");
                    $relation['type'] = 'AbstractRelation::TO_ONE';
                    $this->columns[] = $field;

                    break;
                case AbstractRelation::ONE_TO_MANY:
                    $relation['field'] = $output->input("Insert field name in $related that represents this object in DB: ");
                    $relation['type'] = 'AbstractRelation::ONE_TO_MANY';

                    break;
                case AbstractRelation::MANY_TO_MANY:
                    $relation['field'] = $output->input("Insert field name in pivot table that represents $related in DB: ");
                    $relation['defined_by'] = $output->input("Insert field name in pivot table that represents this object in DB: ");
                    $relation['type'] = 'AbstractRelation::MANY_TO_MANY';

                    $pivot = $output->input('Name of pivot table (empty for default): ');

                    if ($pivot !== '') {
                        $relation['pivot'] = $pivot;
                    }

                    break;
            }

            $relations[] = $relation;
            $message = 'Set one more relation?';
        }

        return $relations;
    }
}
