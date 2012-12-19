<?php
/**
 * File containing the SubtreeCommand class.
 *
 * @copyright Copyright (C) 2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */
namespace EzSystems\CookbookBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand,
    Symfony\Component\Console\Input\InputInterface,
    Symfony\Component\Console\Output\OutputInterface,
    Symfony\Component\Console\Input\InputArgument,
    Symfony\Component\Console\Input\InputOption;

class SubtreeCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this->setName( 'ezpublish:cookbook:subtree' )->setDefinition(
            array(
                new InputArgument( 'operation', InputArgument::REQUIRED, 'Operation to execute, either copy or move' ),
                new InputArgument( 'srcLocationId', InputArgument::REQUIRED, 'A subtree\`s root Location' ),
                new InputArgument( 'destinationParentLocationId', InputArgument::REQUIRED, 'Parent location ID to copy/move to' ),
            )
        );
    }

    protected function execute( InputInterface $input, OutputInterface $output )
    {
        /** @var $repository \eZ\Publish\API\Repository\Repository */
        $repository = $this->getContainer()->get( 'ezpublish.api.repository' );
        $locationService = $repository->getLocationService();

        $repository->setCurrentUser( $repository->getUserService()->loadUser( 14 ) );

        // fetch arguments
        $operation = $input->getArgument( 'operation' ); // copy or move
        $destinationParentLocationId = $input->getArgument( 'destinationParentLocationId' );
        $srcLocationId = $input->getArgument( 'srcLocationId' );

        try
        {
            $srcLocation = $locationService->loadLocation( $srcLocationId );
            $destinationParentLocation = $locationService->loadLocation( $destinationParentLocationId );

            if ( $operation == 'copy' )
            {
                $newLocation = $locationService->copySubtree( $srcLocation, $destinationParentLocation );
            }
            else if ( $operation == 'move' )
            {
                $newLocation = $locationService->moveSubtree( $srcLocation, $destinationParentLocation );
            }
            else
            {
                $output->writeln( "<error>operation must be either copy or move</error>" );
                return;
            }

            print_r( $newLocation );
        }
        catch ( \eZ\Publish\API\Repository\Exceptions\NotFoundException $e )
        {
            $output->writeln( $e->getMessage() );
        }
        catch( \eZ\Publish\API\Repository\Exceptions\UnauthorizedException $e )
        {
            $output->writeln( $e->getMessage() );
        }
    }
}


