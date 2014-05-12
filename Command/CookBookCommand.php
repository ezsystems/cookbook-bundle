<?php
/**
 * File containing the CookBookCommand class.
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

class CookBookCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this->setName( 'ezpublish:cookbook:my_first_command' )->setDefinition(
            array(
                new InputArgument( 'contentId', InputArgument::REQUIRED, 'An existing content id' )
            )
        );
    }

    protected function execute( InputInterface $input, OutputInterface $output )
    {
        $contentId = $input->getArgument( 'contentId' );

        /** @var $repository \eZ\Publish\API\Repository\Repository */
        $repository = $this->getContainer()->get( 'ezpublish.api.repository' );
        $contentService = $repository->getContentService();

        try
        {
            print_r( $contentService->loadContentInfo( $contentId ) );
        }
        catch ( \eZ\Publish\API\Repository\Exceptions\NotFoundException $e )
        {
            $output->writeln( "No content with id $contentId" );
        }
        catch ( \eZ\Publish\API\Repository\Exceptions\UnauthorizedException $e )
        {
            $output->writeln( "Anonymous users are not allowed to read content with id $contentId" );
        }
    }
}

