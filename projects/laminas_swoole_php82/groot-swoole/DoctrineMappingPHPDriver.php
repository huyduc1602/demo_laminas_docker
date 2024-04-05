<?php
namespace GrootSwoole;

use Doctrine\Persistence\Mapping\ClassMetadata;
use \Doctrine\Persistence\Mapping\Driver\FileDriver;

class DoctrineMappingPHPDriver extends FileDriver 
{
    /**
     * @var ClassMetadata
     * @psalm-var ClassMetadata<object>
     */
    protected $metadata;

    /** @param string|array<int, string>|FileLocator $locator */
    public function __construct($locator)
    {
        parent::__construct($locator, '.php');
    }

    /**
     * {@inheritDoc}
     */
    public function loadMetadataForClass(string $className, ClassMetadata $metadata)
    {
        $this->metadata = $metadata;
        
        $entity = new \ReflectionClass($className);
        
        $this->loadMappingFile($entity->getFileName());
    }

    /**
     * {@inheritDoc}
     */
    protected function loadMappingFile(string $file)
    {
        $metadata = $this->metadata;
        include $file;

        return [$metadata->getName() => $metadata];
    }
}
