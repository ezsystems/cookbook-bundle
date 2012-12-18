<?php
/**
 * File containing the BrowseLocationsCommand class.
 *
 * @copyright Copyright (C) 2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */
namespace EzSystems\CookbookBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use eZ\Publish\API\Repository\Values\Content\Location;

/**
 * This commands walks through a subtree and prints out the content names.
 */
class BrowseLocationsCommand extends ContainerAwareCommand
{
    /**
     * @var \eZ\Publish\API\Repository\LocationService
     */
    private $locationService;

    /**
     * @var \eZ\Publish\API\Repository\ContentService
     */
    private $contentService;


    /**
     * This method override configures on input argument for the content id
     */
    protected function configure()
    {
        $this->setName( 'ezpublish:cookbook:browse_locations' )->setDefinition(
            array(
                new InputArgument( 'locationId', InputArgument::REQUIRED, 'Location ID to browse from' )
            )
        );
    }

    /**
     * Prints out the location name, and recursively calls itself on each its children
     *
     * @param \eZ\Publish\API\Repository\Values\Content\Location $location
     * @param int $depth The current depth
     *
     * @param OutputInterface $output
     */
    private function browseLocation( Location $location, OutputInterface $output, $depth = 0 )
    {
        // indent according to depth and write out the name of the content
        $output->write( str_pad( '', $depth ) );
        $output->writeln( $location->contentInfo->name );

        // we request the location's children using the location service, and call browseLocation on each
        $childLocations = $this->locationService->loadLocationChildren( $location );
        foreach ( $childLocations->locations as $childLocation )
        {
            $this->browseLocation( $childLocation, $output, $depth + 1 );
        }
    }

    protected function execute( InputInterface $input, OutputInterface $output )
    {
        /** @var $repository \eZ\Publish\API\Repository\Repository */
        $repository = $this->getContainer()->get( 'ezpublish.api.repository' );
        $this->contentService = $repository->getContentService();
        $this->locationService = $repository->getLocationService();

        // fetch the input argument
        $locationId = $input->getArgument( 'locationId' );

        try
        {
            // load the starting location and browse
            $location = $this->locationService->loadLocation( $locationId );
            $this->browseLocation( $location, $output );
        }
        catch ( \eZ\Publish\API\Repository\Exceptions\NotFoundException $e )
        {
            // react on location was not found
            $output->writeln( "<error>No location found with id $locationId</error>" );
        }
        catch( \eZ\Publish\API\Repository\Exceptions\UnauthorizedException $e )
        {
            // react on permission denied
            $output->writeln( "<error>Anonymous users are not allowed to read location with id $locationId</error>" );
        }
    }
}


