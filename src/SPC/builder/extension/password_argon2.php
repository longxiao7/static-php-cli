<?php

declare(strict_types=1);

namespace SPC\builder\extension;

use SPC\builder\Extension;
use SPC\exception\RuntimeException;
use SPC\util\CustomExt;

#[CustomExt('password-argon2')]
class password_argon2 extends Extension
{
    public function getDistName(): string
    {
        return '';
    }

    public function runCliCheckUnix(): void
    {
        [$ret] = shell()->execWithResult(BUILD_ROOT_PATH . '/bin/php -n -r "assert(defined(\'PASSWORD_ARGON2I\'));"');
        if ($ret !== 0) {
            throw new RuntimeException('extension ' . $this->getName() . ' failed sanity check');
        }
    }

    public function patchBeforeMake(): bool
    {
        $patched = parent::patchBeforeMake();
        if ($this->builder->getLib('libsodium') !== null) {
            $extraLibs = getenv('SPC_EXTRA_LIBS');
            if ($extraLibs !== false) {
                $extraLibs = str_replace(
                    [BUILD_LIB_PATH . '/libargon2.a', BUILD_LIB_PATH . '/libsodium.a'],
                    ['', BUILD_LIB_PATH . '/libargon2.a ' . BUILD_LIB_PATH . '/libsodium.a'],
                    $extraLibs,
                );
                $extraLibs = trim(preg_replace('/\s+/', ' ', $extraLibs)); // normalize spacing
                f_putenv('SPC_EXTRA_LIBS=' . $extraLibs);
                return true;
            }
        }
        return $patched;
    }
}
