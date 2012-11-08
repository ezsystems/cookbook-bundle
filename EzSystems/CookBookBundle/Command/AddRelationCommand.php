<?php
/**
 * File containing the AddRelationCommand class.
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
 * This command adds a relation from a content source to a content destination.
 *
 * @author christianbacher
 */
class AddRelationCommand extends ContainerAwareCommand
{
    /**
     * This method override configures on input argument for the content id
     */
    protected function configure()
    {
        $this->setName( 'ezp_cookbook:addrelation' )->setDefinition(
            array(
                new InputArgument( 'srcContentId' , InputArgument::REQUIRED, 'the source content'),
                new InputArgument( 'destContentId' , InputArgument::REQUIRED, 'the destination content'),
            )
        );
    }

    /**
     * execute the command
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     */
    protected function execute( InputInterface $input, OutputInterface $output )
    {
        // fetch the input arguments
        $srcContentId = $input->getArgument( 'srcContentId' );
        $destContentId = $input->getArgument( 'destContentId' );

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
            // for the given content ids load the corresponding content infos
            $srcContentInfo = $contentService->loadContentInfo( $srcContentId );
            $destContentInfo = $contentService->loadContentInfo( $destContentId );

            // create a draft from the current published version of the source and
            // add a relation to the draft. Then publihh the draft.
            $contentDraft = $contentService->createContentDraft( $srcContentInfo );
            $contentService->addRelation( $contentDraft->versionInfo, $destContentInfo );
            $content = $contentService->publishVersion( $contentDraft->versionInfo );

            print_r( $content ); // prints out the resulting content
        }
        catch( \eZ\Publish\API\Repository\Exceptions\NotFoundException $e )
        {
            // react on content not found
            $output->writeln( $e->getMessage() );
        }
    }
}
