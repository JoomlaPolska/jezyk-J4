<?php

namespace TranslationTest\Command;

use Composer\Script\Event;
use En_GBLocalise;
use JsonException;
use RuntimeException;
use SplFileInfo;
use TranslationTest\TestHelper;

define('_JEXEC', true);

final class Verify
{
    protected static ?array $releases = null;

    protected Event $event;

    protected string $path_tmp = '';
    protected string $path_root = '';
    protected string $path_original = '';

    protected string $testedReleaseTag = '';

    protected TestHelper $helper;

    protected array $translation_constants = [];

    protected int $total_phrases_count = 0;
    protected int $missing_count = 0;
    protected int $obsolete_count = 0;
    protected int $missing_files_count = 0;
    protected int $obsolete_files_count = 0;
    protected static int $changed_files_count = 0;

    protected array $obsolete_phrases = [];
    protected array $missing_phrases = [];

    protected bool $ignore_obsolete = false;
    protected bool $find_untranslated = false;

    protected ?array $plural_source_prefixes = null;
    protected ?array $plural_suffixes = null;

    protected ?string $language_code = null;

    protected static array $changes = [];

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
            echo "Use: `composer test:translation TAG` (where TAG is a tag name from https://github.com/joomla/joomla-cms repository)".PHP_EOL;
            echo "Arguments: ".PHP_EOL;
            echo "\t--ignore-obsolete - Ignore obsolete strings".PHP_EOL;
            echo "\t--find-untranslated - Find phrases that have the same value in both languages (are not translated)".PHP_EOL;
            echo "\t--help|--usage - Shows this screen".PHP_EOL;
            die;
        }

        if(in_array('--ignore-obsolete', $arguments, true)) {
            $this->ignore_obsolete = true;
        }

        if(in_array('--find-untranslated', $arguments, true)) {
            $this->find_untranslated = true;
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
            if( substr_count($arguments[0], '.')<2 && str_ends_with($arguments[0], '-dev') ) {
                $version = substr($arguments[0], 0, stripos($arguments[0], '-'));
                $this->testedReleaseTag = $this->getReleaseTag($version);

                self::write('Dev branch name provided. Using latest tag: '.$this->testedReleaseTag);
            } else {
                $this->testedReleaseTag = $arguments[0];
            }

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
    public static function write(string $text, ?string $file = null): void
    {
        if( !is_null($file) ) {
            if ( !array_key_exists($file, self::$changes) ) {
                self::$changes = array_merge(self::$changes, [$file=>[]]);
            } else {
                self::$changes[$file][] = self::colorize($text) ."\033[39m". PHP_EOL;
            }
        } else {
            echo self::colorize($text) ."\033[39m". PHP_EOL;
        }
    }

    protected static function colorize(string $text): string
    {
        $text = str_ireplace(
            ['<red>',       '<green>',  '<yellow>', '<blue>',   '<magenta>',    '<cyan>',   '<gray>',   '<white>',  '<br>'],
            ["\033[31m",    "\033[32m", "\033[33m", "\033[34m", "\033[35m",     "\033[36m", "\033[90m", "\033[97m", PHP_EOL],
            $text
        );

        return str_ireplace(['</red>','</green>','</yellow>','</blue>','</magenta>','</cyan>','</gray>','</white>'],"\033[39m", $text);
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
                $originalRelative = str_replace(DIRECTORY_SEPARATOR, '/', substr($original, strlen($this->path_root)+1));
                self::write("<red>The translation file $originalRelative doesn't exist in English translation files!</red>", $originalRelative);
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
                $translatedPathRelative = str_replace(DIRECTORY_SEPARATOR, '/', substr($translatedPath, strlen($this->path_root)+1));
                self::write("The translation file <red>$translatedPathRelative</red> doesn't exist in your translation files!", $translatedPathRelative);
                $this->missing_files_count++;
            }
        }
    }

    protected function compare(string $translated_path, string $original_path): void
    {
        $translated = parse_ini_file($translated_path, false, INI_SCANNER_RAW);
        $original = parse_ini_file($original_path, false, INI_SCANNER_RAW);

        $this->total_phrases_count+= count($translated);

        $this->translation_constants = array_merge($this->translation_constants, $translated);

        if( !is_array($translated) ) {
            throw new RuntimeException("Unable to parse translation file: $translated_path");
        }

        if( !is_array($original) ) {
            throw new RuntimeException("Unable to parse original file: $original_path");
        }

        $obsolete_keys = array_diff_key($translated, $original);
        $missing_keys = array_diff_key($original, $translated);
        $untranslated_keys = array_intersect_assoc($translated, $original);

        if( ($obsolete_keys!==[] || $missing_keys!==[]) && !$this->ignore_obsolete ) {
            $translated_relative_path = str_replace(DIRECTORY_SEPARATOR, '/', substr($translated_path, strlen($this->path_root)+1));

            // Find phrases that should be removed
            foreach( $obsolete_keys as $key=>$value ) {

                if( $this->translationConstantIsPlural($key) ) {
                    continue;
                }

                if( !$this->ignore_obsolete ) {
                    self::write("<gray>- $key</gray> was removed", $translated_relative_path);
                }

                $this->obsolete_count++;
                $this->obsolete_phrases[$key] = $translated_path;
            }

            // Find phrases that should be added to translation
            foreach( $missing_keys as $key=>$value ) {

                // This is a plural constant so ignore it
                if( $this->sourceConstantIsPlural($key) ) {
                    continue;
                }

                // This constant has plural translation so ignore it
                if( $this->pluralTranslationExists($key) ) {
                    continue;
                }

                self::write("<red>! $key=\"". $value ."\"</red> is missing in translation", $translated_relative_path);

                $this->missing_count++;
                $this->missing_phrases[$key] = $translated_path;
            }
        }

        if( $untranslated_keys!==[] && $this->find_untranslated ) {
            $translated_relative_path = str_replace(DIRECTORY_SEPARATOR, '/', substr($translated_path, strlen($this->path_root)+1));

            // Find phrases that are present in both languages but are exactly the same
            foreach( $untranslated_keys as $key=>$value ) {

                // Numbers are not translated
                if( is_numeric($value) ) {
                    continue;
                }

                self::write("<yellow>$key = \"". $value ."\"</yellow> is the same as in English", $translated_relative_path);
            }
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

        self::flush();

        self::write(PHP_EOL.str_repeat('=', 64));
        self::write("- Translation was tested against: <white>$this->testedReleaseTag</white>");
        self::write("- Total phrases found: <yellow>".number_format($this->total_phrases_count,0, '', ',').'</yellow>');

        if( self::$changed_files_count ) {
            self::write("- Found <yellow>".self::$changed_files_count."</yellow> translation files changed");
        }

        if( $this->missing_count ) {
            self::write("- <red>Missing $this->missing_count translation phrases</red>");
        }

        if( $this->missing_files_count ) {
            self::write("- <red>Missing $this->missing_files_count translation files</red>");
        }

        if( $this->obsolete_count && $this->ignore_obsolete ) {
            self::write("- Found <yellow>$this->obsolete_count</yellow> obsolete phrases");
        } elseif( $this->obsolete_count && !$this->ignore_obsolete ) {
            self::write("- <red>Found $this->obsolete_count obsolete phrases</red>");
        }

        if( $this->obsolete_files_count && $this->ignore_obsolete ) {
            self::write("- Found <yellow>$this->obsolete_files_count</yellow> obsolete translation files");
        } elseif ( $this->obsolete_files_count && !$this->ignore_obsolete ) {
            self::write("- <red>Found $this->obsolete_files_count obsolete translation files</red>");
        }

        self::write(str_repeat('=', 64));

        if( $this->missing_count || $this->missing_files_count || (($this->obsolete_count || $this->obsolete_files_count) && !$this->ignore_obsolete) ) {

            self::write("Translation <red>test failed!</red>");
            self::write(str_repeat('=', 64));
            self::write('');

            exit(1);
        }

        self::write("Translation <green>test passed.</green>");

        self::write(str_repeat('=', 64));
        self::write('');
    }

    /**
     * @throws JsonException
     */
    private function getReleaseTags(): array
    {
        if( is_null(self::$releases) ) {
            $tags = (new TestHelper())->getURLContents('https://api.github.com/repos/joomla/joomla-cms/releases');
            $tags = json_decode($tags, JSON_OBJECT_AS_ARRAY, 512, JSON_THROW_ON_ERROR);
            self::$releases = array_column($tags, 'tag_name');
        }

        return self::$releases;
    }

    /**
     * @param   string  $version Version including minor (eg. 5.6)
     *
     * @return string
     * @throws JsonException
     */
    private function getReleaseTag(string $version): string
    {
        $tags = $this->getReleaseTags();

        usort($tags, 'version_compare');

        $tags = array_filter($tags, function($tag) use ($version) {
            return str_starts_with($tag, $version) && !str_contains($tag, '-');
        });

        return end($tags);
    }

    private function getLanguageCode(): string
    {
        if( is_null($this->language_code) ) {
            $manifests = glob($this->path_root.'/language/*', GLOB_ONLYDIR);
            $manifests = array_diff($manifests, ['en-GB']);
            $this->language_code = basename(current($manifests));
        }

        return $this->language_code;
    }

    private function translationConstantIsPlural(string $constant): bool
    {
        $suffixes = $this->getPluralSuffixes();
        foreach($suffixes as $suffix) {
            if(str_ends_with($constant, $suffix)) {
                return true;
            }
        }

        return false;
    }

    public function sourceConstantIsPlural(string $constant): bool
    {
        $suffixes = $this->getPluralSourceSuffixes();
        foreach($suffixes as $suffix) {
            if(str_ends_with($constant, $suffix)) {
                return true;
            }
        }

        return false;
    }

    public function pluralTranslationExists(string $key): bool
    {
        $suffixes = $this->getPluralSuffixes();

        foreach($suffixes as $suffix) {
            if( array_key_exists($key.$suffix, $this->translation_constants) ) {
                return true;
            }
        }

        return false;
    }

    private function getPluralSuffixes(): array
    {
        if( is_null($this->plural_suffixes) ) {
            require_once $this->path_root.'/language/'.$this->getLanguageCode().'/localise.php';

            $className = ucfirst(str_replace('-','_', $this->getLanguageCode())).'Localise';
            for($i=0, $ic=200; $i<=$ic; $i++) {
                $suffixes = $className::getPluralSuffixes($i);
                foreach( $suffixes as $suffix ) {
                    $this->plural_suffixes['_'.$suffix] = '';
                }
            }

            $this->plural_suffixes = array_keys( $this->plural_suffixes);
        }

        return $this->plural_suffixes;
    }

    private function getPluralSourceSuffixes(): array
    {
        if( is_null($this->plural_source_prefixes) ) {
            require_once $this->path_original.'/language/en-GB/localise.php';

            for($i=0, $ic=200; $i<=$ic; $i++) {
                $suffixes = En_GBLocalise::getPluralSuffixes($i);
                foreach( $suffixes as $suffix ) {
                    $this->plural_source_prefixes['_'.$suffix] = '';
                }
            }

            $this->plural_source_prefixes = array_keys($this->plural_source_prefixes);
        }

        return $this->plural_source_prefixes;
    }

    /**
     * Flush all the changes details into the console.
     *
     * Note: Required to get ir of empty file entries in the test log.
     *
     * @return void
     */
    protected static function flush(): void
    {
        foreach( self::$changes as $file=>$results ) {
            if( $results!==[] ) {
                self::$changed_files_count++;
                echo self::colorize("<br>There are differences in <yellow>$file</yellow><br>");
                foreach( $results as $result ) {
                    echo $result;
                }
            }
        }
    }

}