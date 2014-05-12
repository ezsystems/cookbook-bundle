<?php
/**
 * File containing the AssignContentToSectionCommand class.
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
 * This command assigns a content to a section.
 */
class AssignContentToSectionCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this->setName( 'ezpublish:cookbook:assign_section' )->setDefinition(
            array(
                new InputArgument( 'contentId', InputArgument::REQUIRED, 'Content ID' ),
                new InputArgument( 'sectionId', InputArgument::REQUIRED, 'Section ID' ),
            )
        );
    }

    protected function execute( InputInterface $input, OutputInterface $output )
    {
        // fetch the the input arguments
        $sectionId = $input->getArgument( 'sectionId' );
        $contentId = $input->getArgument( 'contentId' );

        /** @var $repository \eZ\Publish\API\Repository\Repository */
        $repository = $this->getContainer()->get( 'ezpublish.api.repository' );
        $contentService = $repository->getContentService();
        $sectionService = $repository->getSectionService();

        $repository->setCurrentUser( $repository->getUserService()->loadUser( 14 ) );

        try
        {
            $contentInfo = $contentService->loadContentInfo( $contentId );
            $section = $sectionService->loadSection( $sectionId );
            $sectionService->assignSection( $contentInfo, $section );

            // we need to reload the content info to get the updated version
            $contentInfo = $contentService->loadContentInfo( $contentId );
            $output->writeln( $contentInfo->sectionId );
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