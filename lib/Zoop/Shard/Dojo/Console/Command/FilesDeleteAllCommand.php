<?php
/**
 * @link       http://zoopcommerce.github.io/shard
 * @package    Zoop
 * @license    MIT
 */
namespace Zoop\Shard\Dojo\Console\Command;

use Symfony\Component\Console;

/**
 * Command to remove dojo code files from Doctrine document metadata and resource map.
 *
 * @since   1.0
 * @author  Tim Roediger <superdweebie@gmail.com>
 */
class FilesDeleteAllCommand extends Console\Command\Command
{
    /**
     * @see Console\Command\Command
     */
    protected function configure()
    {
        $this
        ->setName('dojo:files:deleteall')
        ->setDescription('Delete all generated dojo code files from Doctrine document metadata.')
        ->setHelp('Delete all generated dojo code files from Doctrine document metadata.');
    }

    protected function execute(Console\Input\InputInterface $input, Console\Output\OutputInterface $output)
    {
        $serviceLocator = $this->getHelper('serviceLocator')->getServiceLocator();

        //make sure the files are actually saved
        $extension = $serviceLocator->get('extension.dojo');
        $extension->setFlatFileStrategy('delete');

        $resourceMap = $serviceLocator->get('resourceMap');

        if (count($resourceMap->getMap()) == 0){
            $output->write('Nothing to delete' . PHP_EOL);
        }

        $generated = [];
        $continue = true;
        //do the loop in this slightly odd way to accomodate for extra resources being added
        //to the map during loop execution
        while ($continue){
            $continue = false;
            foreach ($resourceMap->getMap() as $name => $config){
                if (in_array($name, $generated)){
                    continue;
                }
                $output->write(
                    sprintf('Deleting resource <info>%s</info>', $name) . PHP_EOL
                );
                $resourceMap->get($name);
                $generated[] = $name;
                $continue = true;
            }
        }
    }
}
