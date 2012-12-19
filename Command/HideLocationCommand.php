<?php
/**
 * File containing the HideLocationCommand class.
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
 * This command demonstrates how to hide and unhide a location (subtree)
 *
 * @author christianbacher
 *
 */
class HideLocationCommand extends ContainerAwareCommand
{
    /**
     * This method override configures on input argument for the content id
     */
    protected function configure()
    {
        $this->setName( 'ezpublish:cookbook:addlocation' )->setDefinition(
            array(
                new InputArgument( 'locationId', InputArgument::REQUIRED, 'An existing location id' ),
            )
        );
    }

    protected function execute( InputInterface $input, OutputInterface $output )
    {
        /** @var $repository \eZ\Publish\API\Repository\Repository */
        $repository = $this->getContainer()->get( 'ezpublish.api.repository' );
        $contentService = $repository->getContentService();
        $locationService = $repository->getLocationService();

        $repository->setCurrentUser( $repository->getUserService()->loadUser( 14 ) );

        // fetch the location argument
        $locationId = $input->getArgument( 'locationId' );

        try
        {
            $location = $contentService->loadContentInfo( $locationId );

            $hiddenLocation = $locationService->hideLocation( $location );
            $output->writeln( "<info>Location after hide:</info>" );
            print_r( $hiddenLocation );

            // unhide the now hidden location
            $unhiddenLocation = $locationService->unhideLocation( $hiddenLocation );
            $output->writeln( "\n<info>Location after unhide:</info>" );
            print_r( $unhiddenLocation );

        }
        catch ( \eZ\Publish\API\Repository\Exceptions\NotFoundException $e )
        {
            $output->writeln( "No location found with id $locationId" );
        }
    }
}
