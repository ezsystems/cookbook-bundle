<?php
/**
 * File containing the CopyContentCommand class.
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
 * this command copies a content object to a new location
 */
class CopyContentCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this->setName( 'ezpublish:cookbook:copy_content' )->setDefinition(
            array(
                new InputArgument( 'contentId', InputArgument::REQUIRED, 'An existing content id' ),
                new InputArgument( 'parentLocationId', InputArgument::REQUIRED, 'An existing parent location (node) id' )
            )
        );
    }

    protected function execute( InputInterface $input, OutputInterface $output )
    {
        /** @var $repository \eZ\Publish\API\Repository\Repository */
        $repository = $this->getContainer()->get( 'ezpublish.api.repository' );
        $contentService = $repository->getContentService();
        $locationService = $repository->getLocationService();

        $repository->setCurrentUser( $repository->getUserService()->loadUser( 14 ) );

        // fetch input arguments
        $parentLocationId = $input->getArgument( 'parentLocationId' );
        $contentId = $input->getArgument( 'contentId' );

        try
        {
            // instantiate a location create struct from the parent
            $locationCreateStruct = $locationService->newLocationCreateStruct( $parentLocationId );
            $contentInfo = $contentService->loadContentInfo( $contentId );

            // copy the content - all versions are also copied. If only a specific version
            // should be copied it can be passed as third parameter
            // NOTE: the children are not copied wit this method - use LocationService::copySubtree instead
            $contentCopy = $contentService->copyContent( $contentInfo, $locationCreateStruct );

            print_r( $contentCopy );
        }
        catch ( \eZ\Publish\API\Repository\Exceptions\NotFoundException $e )
        {
            $output->writeln( $e->getMessage() );
        }
        catch ( \eZ\Publish\API\Repository\Exceptions\UnauthorizedException $e )
        {
            $output->writeln( $e->getMessage() );
        }
    }
}