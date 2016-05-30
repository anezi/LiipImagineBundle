<?php

namespace Anezi\ImagineBundle\Controller;

use Imagine\Exception\RuntimeException;
use Anezi\ImagineBundle\Exception\Imagine\Filter\NonExistingFilterException;
use Anezi\ImagineBundle\Exception\Binary\Loader\NotLoadableException;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Class ImagineController.
 */
class ImagineController extends Controller
{
    /**
     * This action applies a given filter to a given image, optionally saves the image and outputs it to the browser at the same time.
     *
     * @param Request $request
     * @param string  $path
     * @param string  $filter
     *
     * @throws \RuntimeException
     * @throws BadRequestHttpException
     *
     * @return RedirectResponse
     */
    public function filterAction(Request $request, $path, $filter)
    {
        // decoding special characters and whitespaces from path obtained from url
        $path = urldecode($path);
        $resolver = $request->get('resolver');

        try {
            if (!$this->get('anezi_imagine.cache.manager')->isStored($path, $filter, $resolver)) {
                try {
                    $binary = $this->get('anezi_imagine.data.manager')->find($filter, $path);
                } catch (NotLoadableException $e) {
                    $defaultImageUrl = $this->get('anezi_imagine.data.manager')->getDefaultImageUrl($filter);

                    if ($defaultImageUrl) {
                        return new RedirectResponse($defaultImageUrl);
                    }

                    throw new NotFoundHttpException('Source image could not be found', $e);
                }

                $this->get('anezi_imagine.cache.manager')->store(
                    $this->get('anezi_imagine.filter.manager')->applyFilter($binary, $filter),
                    $path,
                    $filter,
                    $resolver
                );
            }

            return new RedirectResponse($this->get('anezi_imagine.cache.manager')->resolve($path, $filter, $resolver), 301);
        } catch (NonExistingFilterException $e) {
            $message = sprintf('Could not locate filter "%s" for path "%s". Message was "%s"', $filter, $path, $e->getMessage());

            if ($this->has('logger')) {
                $this->get('logger')->debug($message);
            }

            throw new NotFoundHttpException($message, $e);
        } catch (RuntimeException $e) {
            throw new \RuntimeException(sprintf('Unable to create image for path "%s" and filter "%s". Message was "%s"', $path, $filter, $e->getMessage()), 0, $e);
        }
    }

    /**
     * This action applies a given filter to a given image, optionally saves the image and outputs it to the browser at the same time.
     *
     * @param Request $request
     * @param string  $hash
     * @param string  $path
     * @param string  $filter
     *
     * @throws \RuntimeException
     * @throws BadRequestHttpException
     *
     * @return RedirectResponse
     */
    public function filterRuntimeAction(Request $request, $hash, $path, $filter)
    {
        $resolver = $request->get('resolver');

        try {
            $filters = $request->query->get('filters', []);

            if (!is_array($filters)) {
                throw new NotFoundHttpException(sprintf('Filters must be an array. Value was "%s"', $filters));
            }

            if (true !== $this->get('anezi_imagine.cache.signer')->check($hash, $path, $filters)) {
                throw new BadRequestHttpException(sprintf(
                    'Signed url does not pass the sign check for path "%s" and filter "%s" and runtime config %s',
                    $path,
                    $filter,
                    json_encode($filters)
                ));
            }

            try {
                $binary = $this->get('anezi_imagine.data.manager')->find($filter, $path);
            } catch (NotLoadableException $e) {
                $defaultImageUrl = $this->get('anezi_imagine.data.manager')->getDefaultImageUrl($filter);

                if ($defaultImageUrl) {
                    return new RedirectResponse($defaultImageUrl);
                }

                throw new NotFoundHttpException(sprintf('Source image could not be found for path "%s" and filter "%s"', $path, $filter), $e);
            }

            $rcPath = $this->get('anezi_imagine.cache.manager')->getRuntimePath($path, $filters);

            $this->get('anezi_imagine.cache.manager')->store(
                $this->get('anezi_imagine.filter.manager')->applyFilter($binary, $filter, [
                    'filters' => $filters,
                ]),
                $rcPath,
                $filter,
                $resolver
            );

            return new RedirectResponse($this->get('anezi_imagine.cache.manager')->resolve($rcPath, $filter, $resolver), 301);
        } catch (NonExistingFilterException $e) {
            $message = sprintf('Could not locate filter "%s" for path "%s". Message was "%s"', $filter, $hash.'/'.$path, $e->getMessage());

            if ($this->has('logger')) {
                $this->get('logger')->debug($message);
            }

            throw new NotFoundHttpException($message, $e);
        } catch (RuntimeException $e) {
            throw new \RuntimeException(sprintf('Unable to create image for path "%s" and filter "%s". Message was "%s"', $hash.'/'.$path, $filter, $e->getMessage()), 0, $e);
        }
    }
}
