<?php declare(strict_types=1);

namespace Wnx\LaravelStats\Statistics;

use Illuminate\Support\Str;

class CodeTestRatio
{
    protected $project;

    public function __construct(ProjectStatistics $projectStatistics)
    {
        $this->project = $projectStatistics;
    }

    public function getRatio() : float
    {
        return round($this->getTestLoc() / $this->getCodeLoc(), 1);
    }

    public function getTestLoc() : float
    {
        return collect($this->project->components())
            ->filter(function ($component, $key) {
                return Str::contains($key, 'Test');
            })
            ->sum('loc');
    }

    public function getCodeLoc() : float
    {
        $codeLoc = collect($this->project->components())
            ->filter(function ($component, $key) {
                return ! Str::contains($key, 'Test');
            })
            ->sum('loc');

        if ($codeLoc === 0) {
            return 1;
        }

        return $codeLoc;
    }

    public function summary() : array
    {
        return [
            "Code LOC: {$this->getCodeLoc()}",
            "Test LOC: {$this->getTestLoc()}",
            "Code to Test Ratio: 1:{$this->getRatio()}",
        ];
    }
}
