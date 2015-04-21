<?php
/**
 * File containing the CreateContentTypeCommand class.
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
use eZ\Publish\Core\Base\Exceptions\NotFoundException;

class CreateContentTypeDraftCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this->setName( 'ezpublish:cookbook:create_content_type_draft' )->setDefinition(
            array(
                new InputArgument( 'identifier', InputArgument::REQUIRED, 'a content type identifier' ),
                new InputArgument( 'group_identifier', InputArgument::REQUIRED, 'a content type group identifier' )
            )
        );
    }

    protected function execute( InputInterface $input, OutputInterface $output )
    {
        /** @var $repository \eZ\Publish\API\Repository\Repository */
        $repository = $this->getContainer()->get( 'ezpublish.api.repository' );
        $contentTypeService = $repository->getContentTypeService();

        $repository->setCurrentUser( $repository->getUserService()->loadUser( 14 ) );

        // fetch command line arguments
        $groupIdentifier = $input->getArgument( 'group_identifier' );
        $contentTypeIdentifier = $input->getArgument( 'identifier' );

        try
        {
            $contentTypeGroup = $contentTypeService->loadContentTypeGroupByIdentifier( $groupIdentifier );
        }
        catch ( \eZ\Publish\API\Repository\Exceptions\NotFoundException $e )
        {
            $output->writeln( "content type group with identifier $groupIdentifier not found" );
            return;
        }

        // instantiate a ContentTypeCreateStruct with the given content type identifier and set parameters
        $contentTypeCreateStruct = $contentTypeService->newContentTypeCreateStruct( $contentTypeIdentifier );
        $contentTypeCreateStruct->mainLanguageCode = 'eng-GB';

        // set names for the content type
        $contentTypeCreateStruct->names = array(
            'eng-GB' => 'New content type',
            // 'ger-DE' => $contentTypeIdentifier . 'ger-DE',
        );

        try
        {
            $contentTypeDraft = $contentTypeService->createContentType( $contentTypeCreateStruct, array( $contentTypeGroup ) );
            $output->writeln( "<info>Content type draft created with ID $contentTypeDraft->id" );
        }
        catch ( \eZ\Publish\API\Repository\Exceptions\UnauthorizedException $e )
        {
            $output->writeln( "<error>" . $e->getMessage() . "</error>" );
        }
        catch ( \eZ\Publish\API\Repository\Exceptions\ForbiddenException $e )
        {
            $output->writeln( "<error>" . $e->getMessage() . "</error>" );
        }
    }
}
