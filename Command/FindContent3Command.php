<?php
/**
 * File containing the FindContent3Command class.
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
use eZ\Publish\API\Repository\Values\Content\Query\Criterion;


/**
 * This command performs a location and content type identifier filter search
 */
class FindContent3Command extends ContainerAwareCommand
{
    /**
     * This method override configures on input argument for the content id
     */
    protected function configure()
    {
        $this->setName( 'ezpublish:cookbook:find_filter' )->setDefinition(
            array(
                new InputArgument( 'contentTypeIdentifier', InputArgument::REQUIRED, 'Content type identifier, one or several seperated by comma. example --contentTypeIdentifier=article,folder' ),
                new InputArgument( 'locationId', InputArgument::REQUIRED, 'Location id' ),
            )
        );
    }

    protected function execute( InputInterface $input, OutputInterface $output )
    {
        /** @var $repository \eZ\Publish\API\Repository\Repository */
        $repository = $this->getContainer()->get( 'ezpublish.api.repository' );
        $searchService = $repository->getSearchService();

        $text = $input->getArgument( 'text' );
        $contentTypeIdentifierList = explode( ',', $input->getArgument( 'contentTypeId' ) );
        $locationId = $input->getArgument( 'locationId' );

        // create the query with subtree and a or condition of content type identifiers criteria
        $query = new \eZ\Publish\API\Repository\Values\Content\Query();
        $locationCriterion = new Criterion\LocationId( $locationId );
        $congtentTypeOr = new Criterion\LogicalOr( array() );

        // Note: ContentTypeIdentifier is available in eZ Publish 5.1+, use ContentTypeId instead to also support 5.0
        foreach ( $contentTypeIdentifierList as $contentTypeIdentifier )
            $congtentTypeOr->criteria[] = new Criterion\ContentTypeIdentifier( $contentTypeIdentifier );

        $query->criterion = new Criterion\LogicalAnd(
            array( $locationCriterion, $congtentTypeOr )
        );

        $result = $searchService->findContent( $query );
        $output->writeln( '<info>Found ' . $result->totalCount . ' items</info>' );
        foreach ( $result->searchHits as $searchHit )
        {
            $output->writeln( "* " . $searchHit->valueObject->contentInfo->name );
        }
    }
}
