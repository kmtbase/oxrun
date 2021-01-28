<?php
/**
 * Created for oxrun
 * Author: Tobias Matthaiou <matthaiou@tobimat.eu>
 * Date: 07.06.17
 * Time: 07:46
 */

namespace Oxrun\Command\Module;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class ReloadCommand extends Command
{

    /**
     * Configures the current command.
     */
    protected function configure()
    {
        $this
            ->setName('module:reload')
            ->setDescription('Deactivate and activate a module')
            ->addArgument('module', InputArgument::REQUIRED, 'Module name')
            ->addOption('force', 'f',InputOption::VALUE_NONE, 'Force reload Module');
    }

    /**
     * Executes the current commandd
     *
     * @param InputInterface $input An InputInterface instance
     * @param OutputInterface $output An OutputInterface instance
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $app = $this->getApplication();

        $clearCommand      = $app->find('cache:clear');
        $deactivateCommand = $app->find('oe:module:deactivate');
        $activateCommand   = $app->find('oe:module:activate');

        $argvInputClearCache = $this->createInputArray($clearCommand, $input);
        $argvInputDeactivate = $this->createInputArray($deactivateCommand, $input, ['module-id' => $input->getArgument('module')]);
        $argvInputActivate   = $this->createInputArray($activateCommand, $input, ['module-id' => $input->getArgument('module')]);

        if ($input->getOption('force')) {
            $argvInputClearCache->setOption('force', true);
        }

        //Run Command
        $clearCommand->execute($argvInputClearCache, $output);
        $deactivateCommand->execute($argvInputDeactivate, $output);
        $clearCommand->execute($argvInputClearCache, $output);
        $activateCommand->execute($argvInputActivate, $output);

        return 0;
    }

    /**
     * @param Command$command
     * @param InputInterface $input
     */
    protected function createInputArray($command, $input, $extraOption = [])
    {
        //default --sho-id
        $command->getDefinition()->addOption(new InputOption('--shop-id', '', InputOption::VALUE_REQUIRED));

        $parameters = array_merge(
            ['--shop-id' => $input->getOption('shop-id')],
            $extraOption
        );

        return new ArrayInput(
            $parameters,
            $command->getDefinition()
        );
    }
}
