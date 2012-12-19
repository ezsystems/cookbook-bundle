<?php
/**
 * File containing the CreateContentCommand class.
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

class UpdateContentCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this->setName( 'ezpublish:cookbook:update_content' )->setDefinition(
            array(
                new InputArgument( 'contentId' , InputArgument::REQUIRED, 'the content to be updated' ),
                new InputArgument( 'newtitle' , InputArgument::REQUIRED, 'the new title of the content' ),
                new InputArgument( 'newbody' , InputArgument::REQUIRED, 'the new body of the content' )
            )
        );
    }

    protected function execute( InputInterface $input, OutputInterface $output )
    {
        /** @var $repository \eZ\Publish\API\Repository\Repository */
        $repository = $this->getContainer()->get( 'ezpublish.api.repository' );
        $contentService = $repository->getContentService();

        $repository->setCurrentUser( $repository->getUserService()->loadUser( 14 ) );

        $contentId = $input->getArgument( 'contentId' );
        $newTitle = $input->getArgument( 'newtitle' );
        $newBody = $input->getArgument( 'newbody' );

        try
        {
            // create a content draft from the current published version
            $contentInfo = $contentService->loadContentInfo( $contentId );
            $contentDraft = $contentService->createContentDraft( $contentInfo );

            // instantiate a content update struct and set the new fields
            $contentUpdateStruct = $contentService->newContentUpdateStruct();
            $contentUpdateStruct->initialLanguageCode = 'eng-GB'; // set language for new version
            $contentUpdateStruct->setField( 'title', $newTitle );
            $contentUpdateStruct->setField( 'body', $newBody );

            // update and publish draft
            $contentDraft = $contentService->updateContent( $contentDraft->versionInfo, $contentUpdateStruct );
            $content = $contentService->publishVersion( $contentDraft->versionInfo );

            print_r( $content );
        }
        catch ( \eZ\Publish\API\Repository\Exceptions\NotFoundException $e )
        {
            $output->writeln( $e->getMessage() );
        }
        catch( \eZ\Publish\API\Repository\Exceptions\ContentFieldValidationException $e )
        {
            $output->writeln( $e->getMessage() );
        }
        catch( \eZ\Publish\API\Repository\Exceptions\ContentValidationException $e )
        {
            $output->writeln( $e->getMessage() );
        }
    }
}
