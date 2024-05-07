<?php declare(strict_types=1);

namespace Bizhost\Authentication\Bundle;

use Bizhost\Authentication\Bundle\DependencyInjection\SymfonyHelpersExtension;
use Symfony\Component\DependencyInjection\Extension\ExtensionInterface;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class AuthenticateBundle extends Bundle
{
    /**
     * @return string
     */
    public function getPath(): string
    {
        return \dirname(__DIR__);
    }

    /**
     * @return ExtensionInterface|null
     */
    public function getContainerExtension(): ?ExtensionInterface
    {
        if($this->extension === null) {
            $this->extension = new SymfonyHelpersExtension;
        }

        return $this->extension;
    }


}
