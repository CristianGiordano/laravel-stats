<?php declare(strict_types=1);

namespace Wnx\LaravelStats\Classifiers;

use Illuminate\Console\Command;
use Wnx\LaravelStats\Contracts\Classifier;
use Wnx\LaravelStats\ReflectionClass;

class CommandClassifier implements Classifier
{
    public function getName()
    {
        return 'Commands';
    }

    public function satisfies(ReflectionClass $class)
    {
        return $class->isSubclassOf(Command::class);
    }
}
