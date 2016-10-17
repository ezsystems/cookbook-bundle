<?php
/**
 * File containing the FindContentCommand class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 * @version //autogentag//
 */
namespace EzSystems\CookbookBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

/**
 * This command performs a simple full text search
 *
 * @author christianbacher
 */
class FindContentCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this->setName( 'ezpublish:cookbook:find_fulltext' )->setDefinition(
            array(
                new InputArgument( 'text', InputArgument::REQUIRED, 'Text to search for' )
            )
        );
    }

    protected function execute( InputInterface $input, OutputInterface $output )
    {
        /** @var $repository \eZ\Publish\API\Repository\Repository */
        $repository = $this->getContainer()->get( 'ezpublish.api.repository' );
        $searchService = $repository->getSearchService();

        $text = $input->getArgument( 'text' );

        $query = new \eZ\Publish\API\Repository\Values\Content\Query();
        // Use 'query' over 'filter' to get hit score (relevancy) and default sorting by it with Solr/Elastic
        $query->query = new Query\Criterion\FullText( $text );

        $result = $searchService->findContent( $query );
        $output->writeln( 'Found ' . $result->totalCount . ' items' );
        foreach ( $result->searchHits as $searchHit )
        {
            $output->writeln( $searchHit->valueObject->contentInfo->name );
        }
    }
}
