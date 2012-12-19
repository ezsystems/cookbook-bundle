<?php
/**
 * File containing the ViewContentMetaDataCommand class.
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

class ViewContentMetaDataCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this->setName( 'ezpublish:cookbook:view_content_metadata' )->setDefinition(
                array(
                        new InputArgument( 'contentId', InputArgument::REQUIRED, 'An existing content id' )
                )
        );
    }


    protected function execute( InputInterface $input, OutputInterface $output )
    {
        /** @var $repository \eZ\Publish\API\Repository\Repository */
        $repository = $this->getContainer()->get( 'ezpublish.api.repository' );
        $contentService = $repository->getContentService();
        $locationService = $repository->getLocationService();
        $urlAliasService = $repository->getURLAliasService();
        $sectionService = $repository->getSectionService();
        $userService = $repository->getUserService();

        $repository->setCurrentUser( $repository->getUserService()->loadUser( 14 ) );

        $contentId = $input->getArgument( 'contentId' );

        try
        {
            $contentInfo = $contentService->loadContentInfo( $contentId );

            // show all locations of the content
            $locations = $locationService->loadLocations( $contentInfo );
            $output->writeln( "<info>LOCATIONS</info>" );
            foreach ( $locations as $location )
            {
                $urlAlias = $urlAliasService->reverseLookup( $location );
                $output->writeln( "  $location->pathString  ($urlAlias->path)" );
            }

            // show all relations of the current version
            $versionInfo = $contentService->loadVersionInfo( $contentInfo );
            $relations = $contentService->loadRelations( $versionInfo );
            if ( count( $relations ) )
            {
                $output->writeln( "<info>RELATIONS</info>" );
                foreach ( $relations as $relation )
                {
                    $name = $relation->destinationContentInfo->name;
                    $output->write( "  Relation of type " . $this->outputRelationType( $relation->type ) . " to content $name" );
                }
            }

            // show meta data
            $output->writeln( "\n<info>METADATA</info>" );
            $output->writeln( "  <info>Name:</info> $contentInfo->name" );
            $output->writeln( "  <info>Type:</info> " .$contentInfo->contentType->identifier );
            $output->writeln( "  <info>Last modified:</info> " . $contentInfo->modificationDate->format( 'Y-m-d' ) );
            $output->writeln( "  <info>Published:</info> ". $contentInfo->publishedDate->format( 'Y-m-d' ) );
            $output->writeln( "  <info>RemoteId:</info> $contentInfo->remoteId" );
            $output->writeln( "  <info>Main Language:</info> $contentInfo->mainLanguageCode" );
            $output->writeln( "  <info>Always available:</info> " . ( $contentInfo->alwaysAvailable ? 'Yes' : 'No' ) );

            $owner = $userService->loadUser( $contentInfo->ownerId );
            $output->writeln( "  <info>Owner:</info> " . $owner->contentInfo->name );

            $section = $sectionService->loadSection( $contentInfo->sectionId );
            $output->writeln( "  <info>Section:</info> $section->name" );

            // show versions
            $versionInfoArray = $contentService->loadVersions( $contentInfo );
            if ( count( $versionInfoArray ) )
            {
                $output->writeln( "\n<info>VERSIONS</info>" );
                foreach ( $versionInfoArray as $versionInfo )
                {
                    $creator = $userService->loadUser( $versionInfo->creatorId );
                    $output->write( "  Version $versionInfo->versionNo " );
                    $output->write( " by " . $creator->contentInfo->name );
                    $output->writeln( " " . $this->outputStatus( $versionInfo->status ) . " " . $versionInfo->initialLanguageCode );
                }
            }
        }
        catch ( \eZ\Publish\API\Repository\Exceptions\NotFoundException $e )
        {
            $output->writeln( "<error>No content with id $contentId</error>" );
        }
        catch ( \eZ\Publish\API\Repository\Exceptions\UnauthorizedException $e )
        {
            $output->writeln( "<error>Anonymous users are not allowed to read content with id $contentId</error>" );
        }
    }

    /**
     * Returns the string version of $status
     *
     * @param int $status
     * @return string
     */
    private function outputStatus( $status )
    {
        switch ( $status )
        {
            case 0:
                return 'DRAFT';
            case 1:
                return 'PUBLISHED';
            case 2:
                return 'ARCHIVED';
            default:
                return "UNKNOWN";
        }
    }


    /**
     * Returns the string version of $relationType
     *
     * @param int $relationType
     * @return string
     */
    private function outputRelationType( $relationType )
    {
        switch ( $relationType )
        {
            case 1:
                return 'COMMON';
            case 2:
                return 'EMBED';
            case 4:
                return 'LINK';
            case 8:
                return 'ATTRIBUTE';
            default:
                return "UNKNOWN";
        }
    }

}
