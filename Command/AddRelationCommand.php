<?php
/**
 * File containing the AddRelationCommand class.
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
 * This command adds a relation from a content source to a content destination.
 */
class AddRelationCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this->setName( 'ezpublish:cookbook:add_relation' )->setDefinition(
            array(
                new InputArgument( 'source', InputArgument::REQUIRED, 'Content ID to create relation from' ),
                new InputArgument( 'destination', InputArgument::REQUIRED, 'Content ID to create relation to' ),
            )
        );
    }

    protected function execute( InputInterface $input, OutputInterface $output )
    {
        /** @var $repository \eZ\Publish\API\Repository\Repository */
        $repository = $this->getContainer()->get( 'ezpublish.api.repository' );
        $contentService = $repository->getContentService();

        // load the admin user and set it as current user in the repository
        $repository->setCurrentUser( $userService = $repository->getUserService()->loadUser( 14 ) );

        // fetch the input arguments
        $sourceContentId = $input->getArgument( 'source' );
        $destinationContentId = $input->getArgument( 'destination' );

        try
        {
            // for the given content ids, load content info
            $sourceContentInfo = $contentService->loadContentInfo( $sourceContentId );
            $destinationContentInfo = $contentService->loadContentInfo( $destinationContentId );

            $contentDraft = $contentService->createContentDraft( $sourceContentInfo );
            $contentService->addRelation( $contentDraft->versionInfo, $destinationContentInfo );
            $content = $contentService->publishVersion( $contentDraft->versionInfo );

            print_r( $content );
        }
        catch ( \eZ\Publish\API\Repository\Exceptions\NotFoundException $e )
        {
            // react on content not found
            $output->writeln( $e->getMessage() );
        }
    }
}
