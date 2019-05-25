<?php

declare(strict_types=1);

namespace Wnx\LaravelStats;

use Exception;
use Throwable;
use Wnx\LaravelStats\Classifiers\Nova\Lens;
use Wnx\LaravelStats\Classifiers\Nova\Action;
use Wnx\LaravelStats\Classifiers\Nova\Filter;
use Wnx\LaravelStats\Classifiers\JobClassifier;
use Wnx\LaravelStats\Classifiers\Nova\Resource;
use Wnx\LaravelStats\Classifiers\DuskClassifier;
use Wnx\LaravelStats\Classifiers\MailClassifier;
use Wnx\LaravelStats\Classifiers\RuleClassifier;
use Wnx\LaravelStats\Classifiers\EventClassifier;
use Wnx\LaravelStats\Classifiers\ModelClassifier;
use Wnx\LaravelStats\Classifiers\PolicyClassifier;
use Wnx\LaravelStats\Classifiers\SeederClassifier;
use Wnx\LaravelStats\Classifiers\CommandClassifier;
use Wnx\LaravelStats\Classifiers\PhpUnitClassifier;
use Wnx\LaravelStats\Classifiers\RequestClassifier;
use Wnx\LaravelStats\Classifiers\ResourceClassifier;
use Wnx\LaravelStats\Classifiers\MigrationClassifier;
use Wnx\LaravelStats\Classifiers\ControllerClassifier;
use Wnx\LaravelStats\Classifiers\MiddlewareClassifier;
use Wnx\LaravelStats\Classifiers\NotificationClassifier;
use Wnx\LaravelStats\Classifiers\EventListenerClassifier;
use Wnx\LaravelStats\Classifiers\BrowserKitTestClassifier;
use Wnx\LaravelStats\Classifiers\ServiceProviderClassifier;
use Wnx\LaravelStats\Contracts\Classifier as ClassifierContract;

class Classifier
{
    private const DEFAULT_CLASSIFIER = [
        ControllerClassifier::class,
        ModelClassifier::class,
        CommandClassifier::class,
        RuleClassifier::class,
        PolicyClassifier::class,
        MiddlewareClassifier::class,
        EventClassifier::class,
        EventListenerClassifier::class,
        MailClassifier::class,
        NotificationClassifier::class,
        JobClassifier::class,
        MigrationClassifier::class,
        RequestClassifier::class,
        ResourceClassifier::class,
        SeederClassifier::class,
        ServiceProviderClassifier::class,
        BrowserKitTestClassifier::class,
        DuskClassifier::class,
        PhpUnitClassifier::class,

        // Nova Classifiers
        Action::class,
        Filter::class,
        Lens::class,
        Resource::class,
    ];

    /**
     * Classify a given Class by an available Classifier Strategy.
     *
     * @param ReflectionClass $class
     *
     * @return string
     */
    public function classify(ReflectionClass $class)
    {
        $mergedClassifiers = array_merge(
            self::DEFAULT_CLASSIFIER,
            config('stats.custom_component_classifier', [])
        );

        foreach ($mergedClassifiers as $classifier) {
            $classifierInstance = new $classifier();

            if (! $this->implementsContract($classifier)) {
                throw new Exception("Classifier {$classifier} does not implement ".ClassifierContract::class.'.');
            }

            try {
                $satisfied = $classifierInstance->satisfies($class);
            } catch (Throwable $exception) {
                $satisfied = false;
            }

            if ($satisfied) {
                return $classifierInstance->getName();
            }
        }

        return 'Other';
    }

    /**
     * Check if a class implements our Classifier Contract.
     *
     * @param  class $classifier
     *
     * @return bool
     */
    protected function implementsContract($classifier) : bool
    {
        return (new \ReflectionClass($classifier))->implementsInterface(ClassifierContract::class);
    }
}
