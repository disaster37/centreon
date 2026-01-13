<?php

/*
 * Copyright 2005 - 2025 Centreon (https://www.centreon.com/)
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 * https://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 *
 * For more information : contact@centreon.com
 *
 */

declare(strict_types=1);

namespace Core\Media\Application\UseCase\DeleteMedia;

use Centreon\Domain\Contact\Interfaces\ContactInterface;
use Core\Application\Common\UseCase\ErrorResponse;
use Core\Media\Application\Exception\MediaException;
use Core\Media\Application\Repository\ReadMediaRepositoryInterface;
use Core\Media\Application\Repository\WriteMediaRepositoryInterface;
use Core\Application\Common\UseCase\PresenterInterface;
use Centreon\Domain\Repository\Interfaces\DataStorageEngineInterface;
use Centreon\Domain\Log\LoggerTrait;
use Core\Application\Common\UseCase\NoContentResponse;
use Core\Application\Common\UseCase\NotFoundResponse;

final class DeleteMedia
{
    use LoggerTrait;

    /*
    * @param ReadMediaRepositoryInterface $readMediaRepository
    * @param WriteMediaRepositoryInterface $writeMediaRepository
    * @param DataStorageEngineInterface $storageEngine
    * @param ContactInterface $user
    */
    public function __construct(
        private readonly ReadMediaRepositoryInterface $readMediaRepository,
        private readonly WriteMediaRepositoryInterface $writeMediaRepository,
        private readonly DataStorageEngineInterface $storageEngine,
        private readonly ContactInterface $user,
    ) {
    }

    /**
     * @param int $mediaId
     * @param PresenterInterface $presenter
     */
    public function __invoke(int $mediaId, PresenterInterface $presenter): void
    {
        try {
            

           $media = $this->readMediaRepository->findById($mediaId);


            if ($media === null) {
                $this->error('Media not found', ['media_id' => $mediaId]);
                $presenter->setResponseStatus(new NotFoundResponse('Media'));

                return;
            }

            try {
                $this->info("Delete media #{$mediaId}");
                $this->writeMediaRepository->delete($media);
            } catch (\Throwable $ex) {
                $this->error("Rollback of 'Delete Media' transaction.");
                $this->error($ex->getMessage());

                throw $ex;
            }
            $presenter->setResponseStatus(new NoContentResponse());
            $this->info(
                'Media deleted',
                [
                    'service_id' => $mediaId,
                    'user_id' => $this->user->getId(),
                ]
            );
        } catch (\Throwable $ex) {
            $presenter->setResponseStatus(new ErrorResponse(MediaException::errorWhileDeletingMedia()));
            $this->error($ex->getMessage(), ['trace' => $ex->getTraceAsString()]);
        }
    }
}
