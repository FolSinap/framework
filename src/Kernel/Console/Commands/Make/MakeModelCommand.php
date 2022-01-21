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
        if (!empty($params = $input->getParameters())) {
            $name = $params[0];
        } else {
            $name = $output->input('Input Model name: ');
        }

        $relations = $this->renderRelations($output);
        $columns = $this->renderColumns($output);

        //todo: add {{|use|}} template
        $stub = $this->replaceStubTemplates([
            'class_name' => $name,
            'namespace' => $this->namespace,
            'relations' => $relations,
            'columns' => $columns,
        ]);

        if ($this->createFile("$name.php", $stub)) {
            $output->success('New Model is created successfully!');
        } else {
            $output->error('Something went wrong.');
        }
    }

    protected function renderColumns(Output $output): string
    {
        $columns = $this->buildColumns($output);

        if (!empty($columns)) {
            $definition = MessageBuilder::getBuilder()
                ->nextLine()
                ->tab()->writeln('protected static array $columns = [')
                ->tab()->foreach($columns, function ($key, $column) {
                        return "'$column',\n";
                    })
                ->dropTab()->writeln("];");
        }

        return $definition ?? '';
    }

    protected function buildColumns(Output $output): array
    {
        if (!$output->confirm('Define columns array?')) {
            return [];
        }

        $columns = [];

        while (($name = $output->input('Column name [Enter to stop]: ')) !== '') {
            $columns[] = $name;
        }

        return $columns;
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
                            return "'$key' => '$value',\n";
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
            $relation['type'] = $type = $output->choose('Choose relation type', AbstractRelation::TYPES);
            $relation['class'] = $related = "$this->namespace\\" . $output->input("Insert related class $this->namespace\\");

            switch ($type) {
                case AbstractRelation::TO_ONE:
                    $relation['field'] = $output->input("Insert field name that represents $related in DB: ");

                    break;
                case AbstractRelation::ONE_TO_MANY:
                    $relation['field'] = $output->input("Insert field name in $related that represents this object in DB: ");

                    break;
                case AbstractRelation::MANY_TO_MANY:
                    $relation['field'] = $output->input("Insert field name in pivot table that represents $related in DB: ");
                    $relation['defined_by'] = $output->input("Insert field name in pivot table that represents this object in DB: ");
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
