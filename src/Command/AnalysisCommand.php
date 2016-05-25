<?php

/**
 * @file
 * Contains \Legovaer\CoverageRunner\Command\AnalysisCommand
 */

namespace Legovaer\CoverageRunner\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Filesystem;

class AnalysisCommand extends Command
{

    private $formats = [
        'clover' => 'clover.xml',
        'html' => 'code-coverage-html-report',
        'xml' => 'code-coverage-xml-report',
    ];

    /**
     * @var OutputInterface
     */
    protected $logger;

    protected $file;

    protected $format;

    private $output = null;

    /** @var  \PHP_CodeCoverage */
    protected $coverage;

    /**
     * @inheritdoc
     */
    protected function configure()
    {
        $this
          ->setName('analysis:command')
          ->setDescription('Perform a code coverage analysis on a php file.')
          ->addArgument(
              'file',
              InputArgument::REQUIRED,
              'The file you want to analyse.'
          )
          ->addOption(
              'format',
              'f',
              InputOption::VALUE_OPTIONAL,
              'The output format (can be html, xml or clover). Multiple values are allowed (E.g. "clover,html")'
          )
          ->addOption(
              'out',
              'o',
              InputOption::VALUE_OPTIONAL,
              'The full path of the destination file (or folder in case of xml or html).'
          )
        ;
    }

    /**
     * @inheritdoc
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->logger = $output;
        if ($this->validateInput($input) !== false) {
            $this->coverage = new \PHP_CodeCoverage();
            $this->coverage->start("current-test");
            exec("php ".$this->file);
            $this->coverage->stop();
            $this->report($this->coverage);
        }
    }

    /**
     * Creates a code coverage report.
     *
     * @param \PHP_CodeCoverage $coverage
     *   The analysis.
     */
    private function report(\PHP_CodeCoverage $coverage)
    {
        global $caRoot;

        $writer = null;
        foreach ($this->format as $format) {
            switch ($format) {
                case "clover":
                    $writer = new \PHP_CodeCoverage_Report_Clover();
                    break;

                case "html":
                    $writer = new \PHP_CodeCoverage_Report_HTML();
                    break;

                case "xml":
                    $writer = new \PHP_CodeCoverage_Report_XML();
                    break;
            }

            $output = is_null($this->output) ? $caRoot.'/'.$this->formats[$format] : $this->output;
            $writer->process($coverage, $output);
            $this->logger->writeln("<info>Saved the $format coverage report to $output</info>");
        }
    }

    /**
     * Validates the user's input.
     *
     * @param InputInterface $input
     *   The user's input.
     * @return bool
     *   True if all input is valid, false if not.
     */
    private function validateInput(InputInterface $input)
    {
        if (!$this->validateFile($input->getArgument('file'))) {

            return false;
        } elseif (!$this->validateFormats($this->getFormats($input->getOption('format')))) {
            return false;
        } else {
            $this->output = $input->getOption('out');

            return true;
        }
    }

    /**
     * Transforms the user input to a list of formats.
     *
     * @param $format
     *   The user's input for format.
     *
     * @return array
     *   An array containing all formats the user has specified. If none were specified, an array containing a basic
     *   value.
     */
    private function getFormats($format)
    {
        return count($format) > 0 ? explode(',', $format) : array("html");
    }

    /**
     * Validate the format input.
     *
     * @param array $formats
     *   A list of formats provided by the user.
     * @return bool
     *   True if the list of formats is valid, false if not.
     */
    private function validateFormats(array $formats)
    {
        foreach ($formats as $format) {
            if (!array_key_exists($format, $this->formats)) {
                $this->logger->writeln("<error>Format $format does not exist.</error>");

                return false;
            }
        }

        $this->format = $formats;

        return true;
    }

    /**
     * Validate the file provided by the user.
     *
     * Checks if the file exists and if the file is a php file.
     *
     * @param string $file
     *   The (full) path to the file that needs to be executed during the analysis.
     *
     * @return bool
     *   True if the given file is valid, false if not.
     */
    private function validateFile($file)
    {
        $isValidFile = true;
        $fileSystem = new Filesystem();
        if (!$fileSystem->exists($file)) {
            $this->logger->writeln("<error>File $file does not exist.</error>");
            $isValidFile = false;
        } else {
            $fileParts = pathinfo($file);
            if ($fileParts['extension'] != "php") {
                $this->logger->writeln("<error>File $file is not a PHP file.</error>");
                $isValidFile = false;
            }
        }
        $this->file = $file;

        return $isValidFile;
    }
}
