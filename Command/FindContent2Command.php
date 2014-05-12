<?php
/**
 * File containing the FindContent2Command class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 * @version //autogentag//
 */
namespace EzSystems\CookbookBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand,
    Symfony\Component\Console\Input\InputInterface,
    Symfony\Component\Console\Output\OutputInterface,
    Symfony\Component\Console\Input\InputArgument,
    Symfony\Component\Console\Input\InputOption,
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
        $this->setName( 'ezpublish:cookbook:find_advanced' )->setDefinition(
            array(
                new InputArgument( 'text', InputArgument::REQUIRED, 'Text to search for in title field' ),
                new InputArgument( 'contentTypeId', InputArgument::REQUIRED, 'Content type id' ),
                new InputArgument( 'locationId', InputArgument::REQUIRED, 'Subtree id' ),
            )
        );
    }

    protected function execute( InputInterface $input, OutputInterface $output )
    {
        /** @var $repository \eZ\Publish\API\Repository\Repository */
        $repository = $this->getContainer()->get( 'ezpublish.api.repository' );
        $searchService = $repository->getSearchService();
        $locationService = $repository->getLocationService();

        $text = $input->getArgument( 'text' );
        $contentTypeId = $input->getArgument( 'contentTypeId' );
        $locationId = $input->getArgument( 'locationId' );

        // create the query with three criteria
        $query = new \eZ\Publish\API\Repository\Values\Content\Query();
        $criterion1 = new Criterion\Subtree( $locationService->loadLocation( $locationId )->pathString );
        $criterion2 = new Criterion\ContentTypeId( $contentTypeId );

        $query->criterion = new Criterion\LogicalAnd(
            array( $criterion1, $criterion2 )
        );

        $result = $searchService->findContent( $query );
        $output->writeln( '<info>Found ' . $result->totalCount . ' items</info>' );
        foreach ( $result->searchHits as $searchHit )
        {
            $output->writeln( "* " . $searchHit->valueObject->contentInfo->name );
        }
    }
}

