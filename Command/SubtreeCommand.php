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

/**
 * With this command a subtree can be copied or moved to another location
 *
 * @author christianbacher
 *
 */
class SubtreeCommand extends ContainerAwareCommand
{
    /**
     * This method override configures on input argument for the content id
     */
    protected function configure()
    {
        $this->setName( 'ezp_cookbook:subtree' )->setDefinition(
            array(
                new InputArgument( 'operation', InputArgument::REQUIRED, 'copy or move' ),
                new InputArgument( 'srcLocationId', InputArgument::REQUIRED, 'An existing location id' ),
                new InputArgument( 'destinationParentLocationId', InputArgument::REQUIRED, 'An existing parent location (node) id' ),
            )
        );
    }

    /**
     * execute the command
     * @param InputInterface $input
     * @param OutputInterface $output
     */
    protected function execute( InputInterface $input, OutputInterface $output )
    {
        // fetch arguments
        $operation = $input->getArgument( 'operation' ); // copy or move
        $destinationParentLocationId = $input->getArgument( 'destinationParentLocationId' );
        $srcLocationId = $input->getArgument( 'srcLocationId' );

        // get the repository from the di container
        $repository = $this->getContainer()->get( 'ezpublish.api.repository' );

        // get the services from the repsitory
        $contentService = $repository->getContentService();
        $locationService = $repository->getLocationService();
        $userService = $repository->getUserService();

        // load the admin user and set it has current user in the repository
        $user = $userService->loadUser( 14 );
        $repository->setCurrentUser( $user );

        try
        {
            // copy or move the src location to the destination location

            $srcLocation = $locationService->loadLocation( $srcLocationId );
            $destinationParentLocation = $locationService->loadLocation( $destinationParentLocationId );

            if( $operation == 'copy' )
            {
                $newLocation = $locationService->copySubtree( $srcLocation, $destinationParentLocation );
            }
            else if( $operation == 'move' )
            {
                $newLocation = $locationService->moveSubtree( $srcLocation, $destinationParentLocation );
            }
            else
            {
                $output->writeln("operation must be copy or move");
                return;
            }

            print_r( $newLocation );
        }
        catch( \eZ\Publish\API\Repository\Exceptions\NotFoundException $e )
        {
            // react on location not found
            $output->writeln( $e->getMessage() );
        }
        catch( \eZ\Publish\API\Repository\Exceptions\UnauthorizedException $e )
        {
            // react on permission denied
            $output->writeln( $e->getMessage() );
        }

    }
}


