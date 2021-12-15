<?php

namespace Fwt\Framework\Kernel\Console;

use Fwt\Framework\Kernel\App as BaseApp;
use Fwt\Framework\Kernel\Container;
use Fwt\Framework\Kernel\Database\Connection;
use Fwt\Framework\Kernel\Database\Database;
use Fwt\Framework\Kernel\ObjectResolver;
use Fwt\Framework\Kernel\Router;

class App extends BaseApp
{
    public array $argv;
    public int $argc;

    public function __construct(string $projectDir, array $argv, int $argc)
    {
        $this->argv = $argv;
        $this->argc = $argc;

        parent::__construct($projectDir);
    }

    public function run(): void
    {
        $output = new Output();
        $answer = $output->print("\e[31msadasd?");
        dd($answer);

//        trim(fgets(STDIN)); // reads one line from STDIN
//
//        fscanf(STDIN, "%d\n", $number); // reads number from STDIN
//
//
//        $stderr = fopen('php://stderr', 'w');
//        dd($stderr);
        echo 'works' . "\n";

//        print "Type your message. Type '.' on a line by itself when you're done.\n";
//
//        $fp = fopen('php://stdin', 'r');
//        $last_line = false;
//        $message = '';
//        while (!$last_line) {
//            $next_line = fgets($fp, 1024); // read the special file to get the user input from keyboard
//            if (".\n" == $next_line) {
//                $last_line = true;
//            } else {
//                $message .= $next_line;
//            }
//        }
//        echo $message;
    }

    protected function bootContainer(): void
    {
        $this->container = Container::getInstance();

        $resolver = $this->container[ObjectResolver::class] = new ObjectResolver();
        $this->container[Router::class] = Router::getRouter($resolver);
        $this->container[Input::class] = Input::getInstance($this->argv);

        $this->container[Connection::class] = new Connection(
            getenv('DB'),
            getenv('DB_HOST'),
            getenv('DB_NAME'),
            getenv('DB_USER'),
            getenv('DB_PASSWORD')
        );
        $this->container[Database::class] = new Database($this->container[Connection::class]);
    }
}
