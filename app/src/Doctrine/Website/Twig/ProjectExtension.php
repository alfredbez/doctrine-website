<?php

declare(strict_types=1);

namespace Doctrine\Website\Twig;

use Doctrine\Website\Projects\Project;
use Doctrine\Website\Projects\ProjectRepository;
use Doctrine\Website\Projects\ProjectVersion;
use Twig_Extension;
use Twig_SimpleFunction;
use function file_exists;
use function ksort;
use function str_replace;
use function strpos;

class ProjectExtension extends Twig_Extension
{
    /** @var ProjectRepository */
    private $projectRepository;

    /** @var string */
    private $sculpinSourcePath;

    public function __construct(ProjectRepository $projectRepository, string $sculpinSourcePath)
    {
        $this->projectRepository = $projectRepository;
        $this->sculpinSourcePath = $sculpinSourcePath;
    }

    public function getFunctions()
    {
        return [
            new Twig_SimpleFunction('get_projects', [$this, 'getProjects']),
            new Twig_SimpleFunction('get_project', [$this, 'getProject']),
            new Twig_SimpleFunction('get_url_version', [$this, 'getUrlVersion']),
        ];
    }

    public function getProjects() : array
    {
        $projects = $this->projectRepository->findAll();

        ksort($projects);

        return $projects;
    }

    public function getProject(string $slug) : Project
    {
        return $this->projectRepository->findOneBySlug($slug);
    }

    public function getUrlVersion(ProjectVersion $projectVersion, string $url, string $currentVersion)
    {
        if (strpos($url, 'current') !== false) {
            $otherVersionUrl = str_replace('current', $projectVersion->getSlug(), $url);
        } else {
            $otherVersionUrl = str_replace($currentVersion, $projectVersion->getSlug(), $url);
        }

        $otherVersionFile = $this->sculpinSourcePath . $otherVersionUrl;

        if (! $this->fileExists($otherVersionFile)) {
            return null;
        }

        return $otherVersionUrl;
    }

    protected function fileExists(string $file) : bool
    {
        return file_exists($file);
    }
}
