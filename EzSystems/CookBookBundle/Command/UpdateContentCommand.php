<?php
/**
 * File containing the CreateContentCommand class.
 *
 * @copyright Copyright (C) 2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */
namespace EzSystems\CookBookBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand,
    Symfony\Component\Console\Input\InputInterface,
    Symfony\Component\Console\Output\OutputInterface,
    Symfony\Component\Console\Input\InputArgument,
    Symfony\Component\Console\Input\InputOption;

/**
 * With thios command the demo content containing a body and a title can be updated
 *
 * @author christianbacher
 */
class UpdateContentCommand extends ContainerAwareCommand
{
    /**
     * This method override configures on input argument for the content id
     */
    protected function configure()
    {
        $this->setName( 'ezp_cookbook:updatecontent' )->setDefinition(
            array(
                new InputArgument( 'contentId' , InputArgument::REQUIRED, 'the content to be updated'),
                new InputArgument( 'newtitle' , InputArgument::REQUIRED, 'the new title of the content'),
                new InputArgument( 'newbody' , InputArgument::REQUIRED, 'the new body of the content')
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
        // fetch input arguments
        $contentId = $input->getArgument( 'contentId' );
        $newtitle = $input->getArgument( 'newtitle' );
        $newbody = $input->getArgument( 'newbody' );

        // get the repository from the di container
        $repository = $this->getContainer()->get( 'ezpublish.api.repository' );

        // get the services from the repsitory
        $contentService = $repository->getContentService();
        $userService = $repository->getUserService();

        // load the admin user and set it has current user in the repository
        $user = $userService->loadUser( 14 );
        $repository->setCurrentUser( $user );

        try
        {
            // create a content draft from the current published version
            $contentInfo = $contentService->loadContentInfo( $contentId );
            $contentDraft = $contentService->createContentDraft( $contentInfo );

            // instanciate a content update struct and set the new fields
            $contentUpdateStruct = $contentService->newContentUpdateStruct();
            $contentUpdateStruct->initialLanguageCode = 'eng-GB'; // set language for new version
            $contentUpdateStruct->setField( 'title', $newtitle );
            $contentUpdateStruct->setField( 'body', $newbody );

            // update and publish draft
            $contentDraft = $contentService->updateContent( $contentDraft->versionInfo, $contentUpdateStruct );
            $content = $contentService->publishVersion( $contentDraft->versionInfo );

            print_r( $content );
        }
        catch( \eZ\Publish\API\Repository\Exceptions\NotFoundException $e )
        {
            // react on content not found
            $output->writeln( $e->getMessage() );
        }
        catch( \eZ\Publish\API\Repository\Exceptions\ContentFieldValidationException $e )
        {
            // react on a field is not valid
            $output->writeln( $e->getMessage() );
        }
        catch( \eZ\Publish\API\Repository\Exceptions\ContentValidationException $e )
        {
            // react on a required field is missing or empty
            $output->writeln( $e->getMessage() );
        }
    }
}
