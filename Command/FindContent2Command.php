<?php
/**
 * File containing the FindContent2Command class.
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
    Symfony\Component\Console\Input\InputOption,
    eZ\Publish\API\Repository\Values\Content\Query\Criterion\Operator,
    eZ\Publish\API\Repository\Values\Content\Query\Criterion;


/**
 * This command performs combined full text, subtree and content type search
 *
 * @author christianbacher
 *
 */
class FindContent2Command extends ContainerAwareCommand
{
    /**
     * This method override configures on input argument for the content id
     */
    protected function configure()
    {
        $this->setName( 'ezpublish:cookbook:find2' )->setDefinition(
            array(
                new InputArgument( 'text', InputArgument::REQUIRED, 'text to search in title field' ),
                new InputArgument( 'contentTypeId', InputArgument::REQUIRED, 'content type id' ),
                new InputArgument( 'locationId', InputArgument::REQUIRED, 'location id' ),
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
        //fetch the input arguments
        $text = $input->getArgument( 'text' );
        $contentTypeId = $input->getArgument( 'contentTypeId' );
        $locationId = $input->getArgument( 'locationId' );

        // get the repository from the di container
        $repository = $this->getContainer()->get( 'ezpublish.api.repository' );

        // get the services from the repsitory
        $searchService = $repository->getSearchService();
        $locationService = $repository->getLocationService();

        // create the query with three critions and print out the result
        $query = new \eZ\Publish\API\Repository\Values\Content\Query();
        $criterion1 = new Criterion\FullText( $text );
        $location = $locationService->loadLocation( $locationId );
        $criterion2 = new Criterion\Subtree( $location->pathString ); // restrict results to belong to the given subtree
        $criterion3 = new Criterion\ContentTypeId( $contentTypeId ); // restrict to the given content type

        $query->criterion = new Criterion\LogicalAND(
                array( $criterion1, $criterion2, $criterion3 )
        );

        $result = $searchService->findContent( $query );
        $output->writeln( 'Found ' . $result->totalCount . ' items' );
        foreach( $result->searchHits as $searchHit )
        {
            $output->writeln( $searchHit->valueObject->contentInfo->name );
        }
    }
}

