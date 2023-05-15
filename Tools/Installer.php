<?php

namespace Blackbit\PimcoreBigcommerceSyncBundle\Tools;

use Blackbit\DataDirectorBundle\lib\Pim\Cli;
use Pimcore\Extension\Bundle\Installer\AbstractInstaller;
use Pimcore\Extension\Bundle\Installer\Exception\InstallationException;
use Pimcore\Model\DataObject\ClassDefinition;
use Pimcore\Model\DataObject\Objectbrick;
use Pimcore\Model\User\Permission\Definition;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpKernel\Bundle\BundleInterface;
use Pimcore\Model\DataObject\ClassDefinition\Service;

class Installer extends AbstractInstaller
{
    const DEFINITIONS_PATH = 'Resources/definitions';
    const OBJECTBRICKS_PATH = 'Resources/objectbricks';
    const DATAPORTS_PATH = 'Resources/dataports';

    private BundleInterface $bundle;
    private string $defenitionFullPath;
    private string $objectbrickFullPath;
    private string $dataportFullPath;

    public function __construct(
        BundleInterface $bundle,
    ) {
        parent::__construct();
        $this->defenitionFullPath = dirname(__DIR__)
            . DIRECTORY_SEPARATOR
            . self::DEFINITIONS_PATH
            . DIRECTORY_SEPARATOR;
        $this->objectbrickFullPath = dirname(__DIR__)
            . DIRECTORY_SEPARATOR
            . self::OBJECTBRICKS_PATH
            . DIRECTORY_SEPARATOR;
        $this->dataportFullPath = dirname(__DIR__)
            . DIRECTORY_SEPARATOR
            . self::DATAPORTS_PATH
            . DIRECTORY_SEPARATOR;
    }

    public function install(): bool
    {
        if (!$this->canBeinstalled()) {
            throw new InstallationException(sprintf('Bundle "%s" can\'t be installed', $this->bundle->getName()));
        }

        $result = true;

        $definitionFiles = $this->getDefinitionFiles($this->defenitionFullPath, 'class');

        if (!count($definitionFiles)) {
            throw new InstallationException(sprintf('Bundle "%s" hasn\'t class definition templates', $this->bundle->getName()));
        }

        $objectbrickFiles = $this->getDefinitionFiles($this->objectbrickFullPath, 'objectbrick');

        if (!count($objectbrickFiles)) {
            throw new InstallationException(sprintf('Bundle "%s" hasn\'t objectbrick definition templates', $this->bundle->getName()));
        }

        try {
            foreach ($definitionFiles as $definitionFile) {
                $definitionString = file_get_contents($this->defenitionFullPath.$definitionFile['file']);

                if (!$definitionString) {
                    throw new InstallationException(sprintf('Failed to install. "%s" definition file is invalid', $definitionFile));
                }

                $definitionArray = json_decode($definitionString, true);

                unset($definitionString);

                if (!is_array($definitionArray)) {
                    throw new InstallationException(sprintf('Failed to install. "%s" definition file has invalid stucture', $definitionFile));
                }

                if (
                    empty($definitionArray['id']) ||
                    ClassDefinition::getById($definitionArray['id']) !== null ||
                    ClassDefinition::getByName($definitionFile['name']) !== null
                ) {
                    throw new InstallationException(
                        sprintf(
                            'Failed to install. The system already has a Class with "%s" name and ID "%s"',
                            ucfirst($definitionArray['id']),
                            $definitionArray['id']
                        )
                    );
                }
            }

            foreach ($objectbrickFiles as $objectbrickFile) {
                $objectbrickString = file_get_contents($this->objectbrickFullPath.$objectbrickFile['file']);

                if (!$objectbrickString) {
                    throw new InstallationException(sprintf('Failed to install. "%s" objectbrick file is invalid', $objectbrickFile));
                }

                $objectbrickArray = json_decode($objectbrickString, true);

                unset($objectbrickString);

                if (!is_array($objectbrickArray)) {
                    throw new InstallationException(sprintf('Failed to install. "%s" objectbrick file has invalid stucture', $definitionFile));
                }

                $brickList = new \Pimcore\Model\DataObject\Objectbrick\Definition\Listing();
                $brickList = $brickList->load();

                foreach ($brickList as $item) {
                    if (strtolower($objectbrickFile['name']) === strtolower($item->getKey())) {
                        throw new \Exception(sprintf('Brick with %s name already exists (lower/upper cases may be different)', $objectbrickFile['name']));
                    }
                }
            }

            $notCreated = [];

            foreach ($definitionFiles as $item) {
                if (!$this->createCless($item)['status']) {
                    $notCreated[] = $this->createCless($item);
                }
            }

            sleep(5);
            foreach ($objectbrickFiles as $item) {
                if (!$this->createObjectbrick($item)['status']) {
                    $notCreated[] = $this->createObjectbrick($item);
                }
            }

            if (count($notCreated)) {
                throw new InstallationException(
                    sprintf(
                        count($notCreated) > 1 ? 'Failed to create "%s" classes or objectbricks' : 'Failed to create "%s" classe or objectbrick',
                        implode(', ', array_column($notCreated, 'name'))
                    )
                );
            }

        } catch (\Exception $e) {
            throw new InstallationException("Failed to install: ".$e);
        }


        $dataportFiles = $this->getDataportFiles();
        //dd($dataportFiles);

        if (!count($dataportFiles)) {
            throw new InstallationException(sprintf('Dataport templates are not exist'));
        }


        return $result;
    }

    public function uninstall()
    {
        if (!$this->canBeUninstalled()) {
            throw new InstallationException(sprintf('Bundle "%s" can\'t be uninstalled', $this->bundle->getName()));
        }

        PimcoreService::deleteWebsiteSetting();
    }

    public function canBeInstalled(): bool
    {
        return true;
    }

    public function canBeUninstalled(): bool
    {
        return $this->isInstalled();
    }

    private function getDefinitionFiles(string $path, string $type): array
    {
        $files = [];

        if (
            count(glob($path . '*')) &&
            $handle = opendir($path)
        ) {
            while (false !== ($file = readdir($handle))) {
                $parts = [];
                if (is_file($path . $file)) {
                    if (1 === preg_match('/^'.$type.'_(.*)_export\.json$/', basename($file), $parts)) {
                        $files[] = [
                            'file' => $parts[0],
                            'name' => $parts[1],
                        ];
                    }
                }
            }

            closedir($handle);
        }

        return $files;
    }

    public function createCless(array $definitionFile): array
    {
        $class = new ClassDefinition();
        $class->setName($definitionFile['name']);
        $class->setId(strtolower($definitionFile['name']));

        $data = file_get_contents($this->defenitionFullPath . $definitionFile['file']);
        $definitionFile['status'] = Service::importClassDefinitionFromJson($class, $data, false, true);

        return $definitionFile;
    }

    public function createObjectbrick(array $objectbrickFile): array
    {
        $string = file_get_contents($this->objectbrickFullPath . $objectbrickFile['file']);
        $data = json_decode($string, true);

        $brickDef = new \Pimcore\Model\DataObject\Objectbrick\Definition();
        $brickDef->setKey($objectbrickFile['name']);
        $brickDef->setTitle($data['title']);
        $brickDef->save();

        $objectbrickFile['status'] = Service::importObjectBrickFromJson($brickDef, $string, false, true);

        return $objectbrickFile;
    }


    private function getDataportFiles(): array
    {
        $files = [];

        if (
            count(glob($this->dataportFullPath . '*')) &&
            $handle = opendir($this->dataportFullPath)
        ) {
            while (false !== ($file = readdir($handle))) {
                if (is_file($this->dataportFullPath . $file)) {
                    $filenameArray = explode('-', basename($file, '.json'));

                    $files[] = [
                        'file' => $this->dataportFullPath . $file,
                        'dataportName' => implode(' ', array_map(
                            function($item) {
                                return ucfirst($item);
                            },  $filenameArray))
                    ];
                }
            }

            closedir($handle);
        }

        return $files;
    }
}
