<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Flex\Configurator;

use Symfony\Flex\Recipe;

/**
 * @author Fabien Potencier <fabien@symfony.com>
 */
class CopyFromRecipeConfigurator extends AbstractConfigurator
{
    public function configure(Recipe $recipe, $config)
    {
        $this->write('Setting configuration and copying files');
        $this->copyFiles($config, $recipe->getFiles(), getcwd());
    }

    public function unconfigure(Recipe $recipe, $config)
    {
        $this->write('Removing configuration and files');
        $this->removeFiles($config, $recipe->getFiles(), getcwd());
    }

    private function copyFiles(array $manifest, array $files, string $to)
    {
        foreach ($manifest as $source => $target) {
            $target = $this->options->expandTargetDir($target);
            if ('/' === substr($source, -1)) {
                $this->copyDir($source, $this->concatenatePathParts([$to, $target]), $files);
            } else {
                $this->copyFile(
                    $this->concatenatePathParts([$to, $target]),
                    $files[$source]['contents'],
                    $files[$source]['executable']
                );
            }
        }
    }

    private function copyDir(string $source, string $target, array $files): void
    {
        foreach ($files as $file => $data) {
            if (0 === strpos($file, $source)) {
                $file = $this->concatenatePathParts([$target, substr($file, strlen($source))]);
                $this->copyFile($file, $data['contents'], $data['executable']);
            }
        }
    }

    private function copyFile(string $to, string $contents, bool $executable): void
    {
        if (file_exists($to)) {
            return;
        }

        if (!is_dir(dirname($to))) {
            mkdir(dirname($to), 0777, true);
        }

        file_put_contents($to, $contents);
        if ($executable) {
            @chmod($to, fileperms($to) | 0111);
        }

        $this->write(sprintf('Created file <fg=green>"%s"</>', $this->relativizePath($to)));
    }

    private function removeFiles(array $manifest, array $files, string $to): void
    {
        foreach ($manifest as $source => $target) {
            $target = $this->options->expandTargetDir($target);

            if ('.git' === $target) {
                // never remove the main Git directory, even if it was created by a recipe
                continue;
            }

            if ('/' === substr($source, -1)) {
                foreach (array_keys($files) as $file) {
                    if (0 === strpos($file, $source)) {
                        $this->removeFile(
                            $this->concatenatePathParts([
                                $to,
                                $target,
                                substr($file, strlen($source))
                            ])
                        );
                    }
                }
            } else {
                $this->removeFile($this->concatenatePathParts([$to, $target]));
            }
        }
    }

    private function removeFile(string $to): void
    {
        @unlink($to);
        $this->write(sprintf('Removed file <fg=green>"%s"</>', $this->relativizePath($to)));

        if (0 === count(glob(dirname($to).'/*', GLOB_NOSORT))) {
            @rmdir(dirname($to));
        }
    }

    private function relativizePath(string $absolutePath): string
    {
        $relativePath = str_replace(getcwd(), '.', $absolutePath);

        return is_dir($absolutePath) ? rtrim($relativePath, '/').'/' : $relativePath;
    }

    private function concatenatePathParts(array $parts): string
    {
        return array_reduce($parts, function (string $initial, string $next): string {
            return rtrim($initial, '/').'/'.ltrim($next, '/');
        }, '');
    }
}
