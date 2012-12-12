<?php
/**
 * File containing the FindContentCommand class.
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
 * This command performs a simple full text search
 *
 * @author christianbacher
 */
class FindContentCommand extends ContainerAwareCommand
{
    /**
     * This method override configures on input argument for the content id
     */
    protected function configure()
    {
        $this->setName( 'ezp_cookbook:find' )->setDefinition(
            array(
                new InputArgument( 'text', InputArgument::REQUIRED, 'text to search' )
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
        // fetch the input arguments
        $text = $input->getArgument( 'text' );

        // get the repository from the di container
        $repository = $this->getContainer()->get( 'ezpublish.api.repository' );

        // get the services from repository
        $searchService = $repository->getSearchService();

        // create and execute the query and print out the result
        $query = new \eZ\Publish\API\Repository\Values\Content\Query();
        $query->criterion = new \eZ\Publish\API\Repository\Values\Content\Query\Criterion\FullText( $text );

        $result = $searchService->findContent( $query );
        $output->writeln( 'Found ' . $result->totalCount . ' items' );
        foreach( $result->searchHits as $searchHit )
        {
            $output->writeln( $searchHit->valueObject->contentInfo->name );
        }
    }
}