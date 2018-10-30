<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Phpml\Classification\SVC;
use Phpml\CrossValidation\StratifiedRandomSplit;
use Phpml\Dataset\ArrayDataset;
use Phpml\Dataset\CsvDataset;
use Phpml\FeatureExtraction\TfIdfTransformer;
use Phpml\FeatureExtraction\TokenCountVectorizer;
use Phpml\Metric\Accuracy;
use Phpml\SupportVectorMachine\Kernel;
use Phpml\Tokenization\WordTokenizer;

class PhpMl extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:ml';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'PHP Machine Learn Test';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        //
        $dataset          = new CsvDataset(storage_path('data/languages.csv'), 1);
        $vectorizer       = new TokenCountVectorizer(new WordTokenizer());
        $tfIdfTransformer = new TfIdfTransformer();
        $samples          = [];
        foreach ($dataset->getSamples() as $sample) {
            $samples[] = $sample[0];
        }
        $vectorizer->fit($samples);
        $vectorizer->transform($samples);
        $tfIdfTransformer->fit($samples);
        $tfIdfTransformer->transform($samples);
        $dataset     = new ArrayDataset($samples, $dataset->getTargets());
        $randomSplit = new StratifiedRandomSplit($dataset, 0.1);
        $classifier  = new SVC(Kernel::RBF, 10000);
        $classifier->train($randomSplit->getTrainSamples(), $randomSplit->getTrainLabels());
        $testSamples     = $randomSplit->getTestSamples();
        $predictedLabels = $classifier->predict($testSamples);
        // var_export($testSamples);
        // echo PHP_EOL;
        var_export($predictedLabels);
        echo PHP_EOL;
        echo 'Accuracy: ' . Accuracy::score($randomSplit->getTestLabels(), $predictedLabels), PHP_EOL;

    }

}
