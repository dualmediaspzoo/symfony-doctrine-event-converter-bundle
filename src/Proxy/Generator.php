<?php

namespace DualMedia\DoctrineEventConverterBundle\Proxy;

use DualMedia\DoctrineEventConverterBundle\Event\AbstractEntityEvent;
use DualMedia\DoctrineEventConverterBundle\Exception\Proxy\DirectoryNotWritable;
use DualMedia\DoctrineEventConverterBundle\Exception\Proxy\NotProxyClassException;
use DualMedia\DoctrineEventConverterBundle\Exception\Proxy\TargetClassFinalException;
use DualMedia\DoctrineEventConverterBundle\Exception\Proxy\TargetClassNamingSchemeInvalidException;

/**
 * Loosely based on Doctrine's EntityGenerator.
 */
class Generator
{
    public const string PROXY_NS = 'DualMedia\\DoctrineEventConverterProxy';

    public const string TEMPLATE = <<<EOF
<?php

namespace <namespace>;

/**
<doc> */
class <class> extends \<parent> <interfaces> {}
EOF;

    public const array DOC_TEMPLATE = [
        'WARNING! Proxy class generated automatically, Do not modify!',
        '',
        'Event class for event <event>',
        '@final',
    ];

    public function __construct(
        private readonly string $proxyDirectory,
    ) {
    }

    /**
     * @template T of AbstractEntityEvent
     *
     * @param class-string<T> $class
     * @param list<string> $interfaces
     *
     * @return class-string<T>
     *
     * @throws TargetClassFinalException
     * @throws TargetClassNamingSchemeInvalidException
     * @throws DirectoryNotWritable
     * @throws \ReflectionException
     *
     * @see AbstractEntityEvent
     */
    public function generateProxyClass(
        string $class,
        string $eventName,
        array $interfaces = [],
    ): string {
        $reflection = new \ReflectionClass($class);

        if ($reflection->isFinal()) {
            throw TargetClassFinalException::new([$class]);
        }

        $eventName = ucfirst($eventName);

        $name = self::splitClassName($reflection->getShortName());
        $namespace = self::getNamespace($class);
        $parameters = [
            '<class>' => $classNew = $name[0].$eventName.$name[1],
            '<parent>' => $class,
            '<namespace>' => $namespace,
            '<interfaces>' => '',
        ];

        if (!empty($interfaces)) {
            $parameters['<interfaces>'] = 'implements '.implode(', ', array_map(static fn (string $i) => '\\'.ltrim($i, '\\'), $interfaces));
        }

        $doc = self::DOC_TEMPLATE;

        /**
         * @var class-string<T> $fqcn
         */
        $fqcn = $namespace.'\\'.$classNew;
        $fileName = $this->proxyDirectory.DIRECTORY_SEPARATOR.str_replace('\\', '', mb_substr($fqcn, mb_strlen(self::PROXY_NS))).'.php';

        $parameters['<doc>'] = ' *'.implode(' *', array_map(static fn (string $s) => mb_strlen($s) ? ' '.$s."\n" : "\n", $doc));
        $parameters['<doc>'] = str_replace('<event>', $eventName, $parameters['<doc>']);
        $parentDirectory = dirname($fileName);

        if (!is_dir($parentDirectory) && (false === @mkdir($parentDirectory, 0775, true))) {
            throw new DirectoryNotWritable($this->proxyDirectory);
        }

        if (!is_writable($parentDirectory)) {
            throw new DirectoryNotWritable($this->proxyDirectory);
        }

        $proxy = strtr(self::TEMPLATE, $parameters);

        file_put_contents($fileName, $proxy);
        @chmod($fileName, 0664);

        return $fqcn;
    }

    /**
     * @throws TargetClassNamingSchemeInvalidException
     */
    public static function getProxyFqcn(
        string $class,
        string $eventName,
    ): string {
        $exploded = explode('\\', $class);
        end($exploded);

        $name = self::splitClassName(current($exploded));
        $namespace = self::getNamespace($class);

        return $namespace.'\\'.$name[0].ucfirst($eventName).$name[1];
    }

    /**
     * @return string[]
     *
     * @throws TargetClassNamingSchemeInvalidException
     */
    public static function splitClassName(
        string $classShort,
    ): array {
        if (false === ($pos = mb_strpos($classShort, 'Event'))) {
            throw TargetClassNamingSchemeInvalidException::new([$classShort]);
        }

        return [
            mb_substr($classShort, 0, $pos),
            'Event',
        ];
    }

    /**
     * @throws NotProxyClassException
     */
    public function resolveFilePath(
        string $class,
    ): string {
        if (!str_starts_with($class, self::PROXY_NS)) {
            throw NotProxyClassException::new([$class]);
        }

        $classRelative = mb_substr($class, mb_strlen(self::PROXY_NS));

        return $this->proxyDirectory.DIRECTORY_SEPARATOR.str_replace('\\', '', $classRelative).'.php';
    }

    public static function getNamespace(
        string $class,
    ): string {
        $exploded = explode('\\', $class);
        array_pop($exploded);

        return self::PROXY_NS.'\\'.implode('\\', $exploded);
    }
}
