<?php

namespace TranslationTest\Command;

use Composer\Script\Event;
use En_GBLocalise;
use Exception;
use InvalidArgumentException;
use JsonException;
use RuntimeException;
use SplFileInfo;
use TranslationTest\TestHelper;
use FilesystemIterator;

final class Verify
{
    protected Event $event;

    protected string $path_tmp = '';
    protected string $path_root = '';
    protected string $path_original = '';

    protected string $testedReleaseTag = '';

    protected TestHelper $helper;

    protected int $total_phrases_count = 0;
    protected int $missing_count = 0;
    protected int $obsolete_count = 0;
    protected int $missing_files_count = 0;
    protected int $obsolete_files_count = 0;
    protected int $changed_files_count = 0;

    protected array $obsolete_phrases = [];
    protected array $missing_phrases = [];

    protected bool $ignore_obsolete = false;

    protected ?array $en_plural_suffixes = null;
    protected ?array $plural_suffixes = null;

    protected ?string $language_code = null;

    /**
     * @throws JsonException
     */
    public function __construct(Event $event)
    {
        $this->event = $event;

        $this->helper = new TestHelper();

        // Prepare temp directory
        $this->path_root = dirname(__DIR__, 3);
        $this->path_tmp = $this->path_root.'/tmp';
        $this->path_original = $this->path_tmp.'/joomla-cms';

        if(!is_dir($this->path_tmp) && !mkdir($concurrentDirectory = $this->path_tmp) && !is_dir($concurrentDirectory)) {
            throw new RuntimeException(sprintf('Directory "%s" was not created', $concurrentDirectory));
        }

        $arguments = $event->getArguments();

        if( array_intersect(['--help','--usage'], $arguments)!==[] ) {
            echo PHP_EOL;
            echo "Use: `composer test:translation TAG` (where where TAG is a tag name from https://github.com/joomla/joomla-cms repository)".PHP_EOL;
            echo "Arguments: ".PHP_EOL;
            echo "\t--ignore-obsolete - Ignore obsolete strings".PHP_EOL;
            echo "\t--help|--usage - Shows this screen".PHP_EOL;
            die;
        }

        if(array_intersect(['--ignore-obsolete'], $arguments)!==[]) {
            $this->ignore_obsolete = true;
        }

        if( !array_key_exists(0, $arguments) && !file_exists($this->path_root.'/.test-against') ) {

            $tags = $this->getReleaseTags();
            $tags = implode(', ', $tags);

            self::write(
            "\n<red>Provide a tag name</red> as a parameter for this function eg ".
                "`composer test:translation 4.4.0` or create a file /tmp/.test-against containing a name".
                "of the tag from https://github.com/joomla/joomla-cms repository that you want to test against.\n\n".
                "Available tags:\n$tags\n");

            exit(500);
        }

        if( array_key_exists(0, $arguments) && $arguments[0]!=='' ) {
            $this->testedReleaseTag = $arguments[0];
        } else {
            $this->testedReleaseTag = file_get_contents($this->path_tmp.'/.test-against');

            if( $this->testedReleaseTag === '' ) {
                throw new RuntimeException("Provide a tag name from https://github.com/joomla/joomla-cms repository in /tmp/.test-against");
            }
        }

        $this->downloadRelease();
        $this->compareTranslations();
    }

    /**
     * Write line to console.
     *
     * @param   string  $text  Text.
     */
    public static function write(string $text): void
    {
        echo self::colorize($text) ."\033[39m". PHP_EOL;
    }

    protected static function colorize(string $text): string
    {
        $text = str_ireplace(
            ['<red>','<green>','<yellow>','<blue>','<magenta>','<cyan>','<gray>'],
            ["\033[31m","\033[32m","\033[33m","\033[34m","\033[35m","\033[36m","\033[90m"],
            $text);
        return str_ireplace(['</red>','</green>','</yellow>','</blue>','</magenta>','</cyan>','</gray>'],"\033[39m", $text);
    }

    /**
     * Execute the builder.
     */
    public static function execute(Event $event): void
    {
        new self($event);
    }

    public function getDownloadedTagName(): string
    {
        $tag = shell_exec("cd tmp/joomla-cms && git tag");

        return trim($tag);
    }

    /**
     * @throws JsonException
     */
    protected function downloadRelease(): void
    {

        // Prepare original code directory
        if( is_dir($this->path_tmp.'/joomla-cms') ) {

            $downloadedReleaseTag = $this->getDownloadedTagName();

            if( $downloadedReleaseTag!==$this->testedReleaseTag ) {
                $this->helper->removeDirectory($this->path_tmp);

                // Clone target branch
                system("git clone --depth 1 --branch {$this->testedReleaseTag} --single-branch https://github.com/joomla/joomla-cms.git {$this->path_tmp}/joomla-cms");
            }
        } else {

            // Clone target branch
            system("git clone --depth 1 --branch {$this->testedReleaseTag} --single-branch https://github.com/joomla/joomla-cms.git {$this->path_tmp}/joomla-cms");
        }
    }

    protected function collectTranslationFiles(string $path, array &$files): array
    {
        $directory = new \RecursiveDirectoryIterator($path, \FilesystemIterator::SKIP_DOTS);
        $iterator = new \RecursiveIteratorIterator($directory);
        /**
         * @var SplFileInfo $info
         */
        foreach ($iterator as $info) {

            if( $info->isDir() ) {
                continue;
            }

            if( $info->getExtension()!=='ini' ) {
                continue;
            }

            $files[] = $info->getPathname();
        }

        return $files;
    }

    protected function findOriginalPath(string $translation_path): string
    {
        $relative = substr($translation_path, strlen($this->path_root));
        $original = $this->path_original.$relative;

        $basename = pathinfo($relative,PATHINFO_BASENAME);
        $directory = dirname($original, 1);
        if( preg_match('/^[a-z]{2,3}-[A-Z]{2,3}$/', basename($directory)) ) {
            return dirname($original, 2).'/en-GB/'.$basename;
        }

        return $directory.'/'.$basename;
    }

    protected function findTranslationPath(string $original_path): string
    {
        $relative = substr($original_path, strlen($this->path_tmp.'/joomla-cms'));
        $translation = $this->path_root.$relative;

        $basename = pathinfo($relative,PATHINFO_BASENAME);
        $directory = dirname($translation, 1);
        if( preg_match('/^[a-z]{2,3}-[A-Z]{2,3}$/', basename($directory)) ) {
            return dirname($translation, 2).'/pl-PL/'.$basename;
        }

        return $directory.'/'.$basename;
    }

    protected function processTranslationFiles(array $files): void
    {
        foreach( $files as $translated )
        {
            $original = $this->findOriginalPath($translated);
            if( !file_exists($original) ) {
                self::write("<red>The translation file $original doesn't exist in English translation files!</red>");
                $this->obsolete_files_count++;

                continue;
            }

            $this->compare($translated, $original);
        }
    }

    protected function processSourceFiles(array $files): void
    {
        foreach( $files as $source_path )
        {
            $translatedPath = $this->findTranslationPath($source_path);
            if( !file_exists($translatedPath) ) {
                self::write("<red>The translation file $translatedPath doesn't exist in your translation files!</red>");
                $this->missing_files_count++;
            }
        }
    }

    protected function compare(string $translated_path, string $original_path): void
    {
        $translated = parse_ini_file($translated_path, false, INI_SCANNER_RAW);
        $original = parse_ini_file($original_path, false, INI_SCANNER_RAW);

        $this->total_phrases_count+= count($translated);

        if( !is_array($translated) ) {
            throw new RuntimeException("Unable to parse translation file: $translated_path");
        }

        if( !is_array($original) ) {
            throw new RuntimeException("Unable to parse original file: $original_path");
        }

        $obsolete_keys = array_diff_key($translated, $original);
        $missing_keys = array_diff_key($original, $translated);

        if( $obsolete_keys!==[] || $missing_keys!==[] ) {
            self::write('');
            $translated_relative_path = substr($translated_path, strlen($this->path_root)+1);
            self::write("There are differences in <yellow>$translated_relative_path</yellow>");
            $this->changed_files_count++;
        }

        foreach( $obsolete_keys as $key=>$value ) {
            if( !$this->ignore_obsolete ) {
                self::write("<gray>- $key</gray> was removed");
            }
            $this->obsolete_count++;
            $this->obsolete_phrases[$key] = $translated_path;
        }

        foreach( $missing_keys as $key=>$value ) {
            self::write("<red>! $key=\"{$missing_keys[$key]}\" </red> is missing in translation");
            $this->missing_count++;
            $this->missing_phrases[$key] = $translated_path;
        }
    }

    protected function compareTranslations(): void
    {

        // Process Translation
        $files = [];
        $this->collectTranslationFiles($this->path_root.'/administrator', $files);
        $this->collectTranslationFiles($this->path_root.'/language', $files);
        $this->collectTranslationFiles($this->path_root.'/api', $files);
        $this->collectTranslationFiles($this->path_root.'/installation', $files);
        $this->processTranslationFiles($files);

        // Process Source files
        $files = [];
        $this->collectTranslationFiles($this->path_original.'/administrator', $files);
        $this->collectTranslationFiles($this->path_original.'/language', $files);
        $this->collectTranslationFiles($this->path_original.'/api', $files);
        $this->collectTranslationFiles($this->path_original.'/installation', $files);
        $this->processSourceFiles($files);

        $intersect = array_intersect_key($this->obsolete_phrases, $this->missing_phrases);

        foreach( $intersect as $key=>$value ) {
            self::write("<yellow>$key</yellow> was moved to {$this->missing_phrases[$key]}");
        }

        if( $this->missing_count ) {

            self::write(PHP_EOL. "Translation <red>test not passed!</red>: ");
            self::write("- Total phrases found: <yellow>".number_format($this->total_phrases_count,0, '', ',')).'</yellow>';

            if( $this->changed_files_count ) {
                self::write("- Found <yellow>$this->changed_files_count</yellow> translation files changed");
            }

            self::write("- Missing <yellow>$this->missing_count</yellow> translation phrases");

            if( $this->missing_files_count ) {
                self::write("- Missing <yellow>$this->missing_files_count</yellow> translation files");
            }
            if( $this->obsolete_count && !$this->ignore_obsolete ) {
                self::write("- Found <yellow>$this->obsolete_count</yellow> obsolete phrases");
            }
            if( $this->obsolete_files_count ) {
                self::write("- Found <yellow>$this->obsolete_files_count</yellow> obsolete translation files");
            }

            exit(1);
        }

        self::write(PHP_EOL.
            "Translation <green>testpassed.</green>: ".PHP_EOL.
            "- Total phrases found: <yellow>".number_format($this->total_phrases_count,0, '', ',')).'</yellow>';
    }

    /**
     * @throws JsonException
     */
    private function getReleaseTags(): array
    {
        $tags = (new TestHelper())->getURLContents('https://api.github.com/repos/joomla/joomla-cms/releases');
        $tags = json_decode($tags, JSON_OBJECT_AS_ARRAY, 512, JSON_THROW_ON_ERROR);

        return array_column($tags, 'tag_name');
    }

    private function getPluralEnglishSuffixes(): array
    {
        if( is_null($this->en_plural_suffixes) ) {
            require_once $this->path_original.'/language/en-GB/localise.php';

            $suffixes = [];
            for($i=0, $ic=200; $i<=$ic; $i++) {
                $suffixes[] = En_GBLocalise::getPluralSuffixes($i);
            }

            $this->en_plural_suffixes = array_unique($suffixes);
        }

        return $this->en_plural_suffixes;
    }

    private function getLanguageCode(): string
    {
        if( is_null($this->language_code) ) {
            $manifests = glob($this->path_root.'/language', GLOB_ONLYDIR);
            $manifests = array_diff($manifests, ['en-GB']);
            $this->language_code = current($manifests);
        }

        return $this->language_code;
    }

    private function getPluralSuffixes(): array
    {
        if( is_null($this->plural_suffixes) ) {
            var_dump($this->getLanguageCode());die;
            require_once $this->path_original.'/language/'.$this->getLanguageCode().'/localise.php';

            $suffixes = [];
            for($i=0, $ic=200; $i<=$ic; $i++) {
                $className = ucfirst($this->getLanguageCode()).'Localise';
                $suffixes[] = $$className::getPluralSuffixes($i);
            }

            $this->plural_suffixes = array_unique($suffixes);
        }

        return $this->plural_suffixes;
    }

}