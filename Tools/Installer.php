<?php

namespace Blackbit\PimcoreBigcommerceSyncBundle\Tools;

use Blackbit\DataDirectorBundle\lib\Pim\Cli;
use Blackbit\PimcoreBigcommerceSyncBundle\Services\PimcoreService;
use Pimcore\Extension\Bundle\Installer\AbstractInstaller;
use Pimcore\Extension\Bundle\Installer\Exception\InstallationException;
use Pimcore\Model\DataObject\ClassDefinition;
use Pimcore\Model\User\Permission\Definition;
use Psr\Log\LoggerInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpKernel\Bundle\BundleInterface;
use Pimcore\Model\DataObject\ClassDefinition\Service;

class Installer extends AbstractInstaller
{
    const DEFINITION_PATH = 'Resources/definitions';
    const DATAPORTS_PATH = 'Resources/dataports';

    private LoggerInterface $logger;
    private BundleInterface $bundle;
    private string $defenitionFullPath;
    private string $dataportFullPath;

    public function __construct(
        BundleInterface $bundle,
        LoggerInterface $logger
    ) {
        parent::__construct();
        $this->defenitionFullPath = dirname(__DIR__)
            . DIRECTORY_SEPARATOR
            . self::DEFINITION_PATH
            . DIRECTORY_SEPARATOR;
        $this->dataportFullPath = dirname(__DIR__)
            . DIRECTORY_SEPARATOR
            . self::DATAPORTS_PATH
            . DIRECTORY_SEPARATOR;
    }

    public function install(): bool
    {
//        PimcoreService::deleteWebsiteSetting();
        PimcoreService::createWebsiteSetting();
//        if (!$this->canBeinstalled()) {
//            throw new InstallationException(sprintf('Bundle "%s" can\'t be installed', $this->bundle->getName()));
//        }
//
        $result = true;
//
//        $definitionFiles = $this->getDefinitionFiles();
//
//        if (!count($definitionFiles)) {
//            throw new InstallationException(sprintf('Bundle "%s" hasn\'t class definition templates', $this->bundle->getName()));
//        }
//
//        try {
//            foreach ($definitionFiles as $definitionFile) {
//                $definitionString = file_get_contents($this->defenitionFullPath.$definitionFile['file']);
//
//                if (!$definitionString) {
//                    throw new InstallationException(sprintf('Failed to install. "%s" definition file is invalid', $definitionFile));
//                }
//
//                $definitionArray = json_decode($definitionString, true);
//
//                unset($definitionString);
//
//                if (!is_array($definitionArray)) {
//                    throw new InstallationException(sprintf('Failed to install. "%s" definition file has invalid stucture', $definitionFile));
//                }
//
//                if (
//                    empty($definitionArray['id']) ||
//                    ClassDefinition::getById($definitionArray['id']) !== null ||
//                    ClassDefinition::getByName($definitionFile['className']) !== null
//                ) {
//                    throw new InstallationException(
//                        sprintf(
//                            'Failed to install. The system already has a DataObject with "%s" name and ID "%s"',
//                            ucfirst($definitionArray['id']),
//                            $definitionArray['id']
//                        )
//                    );
//                }
//            }
//
//            $notCreated = [];
//            foreach ($definitionFiles as $item) {
//                if (!$this->createCless($item)['status']) {
//                    $notCreated[] = $this->createCless($item);
//                }
//            }
//
//            if (count($notCreated)) {
//                throw new InstallationException(
//                    sprintf(
//                        count($notCreated) > 1 ? 'Failed to create "%s" classes' : 'Failed to create "%s" class',
//                        implode(', ', array_column($notCreated, 'className'))
//                    )
//                );
//            }
//
//            PimcoreService::createWebsiteSetting();
//        } catch (\Exception $e) {
//            throw new InstallationException("Failed to install: ".$e);
//        }


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

    private function getDefinitionFiles(): array
    {
        $files = [];

        if (
            count(glob($this->defenitionFullPath . '*')) &&
            $handle = opendir($this->defenitionFullPath)
        ) {
            while (false !== ($file = readdir($handle))) {
                $parts = [];
                if (is_file($this->defenitionFullPath . $file)) {
                    if (1 === preg_match('/^class_(.*)_export\.json$/', basename($file), $parts)) {
                        $files[] = [
                            'file' => $parts[0],
                            'className' => $parts[1],
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
        $class->setName($definitionFile['className']);
        $class->setId(strtolower($definitionFile['className']));

        $data = file_get_contents($this->defenitionFullPath . $definitionFile['file']);
        $definitionFile['status'] = Service::importClassDefinitionFromJson($class, $data, false, true);

        return $definitionFile;
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
