<?php namespace Edutalk\Base\StaticBlocks\Support;

use Edutalk\Base\StaticBlocks\Repositories\Contracts\StaticBlockRepositoryContract;
use Edutalk\Base\StaticBlocks\Repositories\StaticBlockRepository;

class StaticBlockShortcodeRenderer
{
    /**
     * @var StaticBlockRepository
     */
    protected $repository;

    public function __construct(StaticBlockRepositoryContract $repository)
    {
        $this->repository = $repository;
    }

    /**
     * @var \Edutalk\Base\Shortcode\Compilers\Shortcode $shortcode
     * @var string $content
     * @var \Edutalk\Base\Shortcode\Compilers\ShortcodeCompiler $compiler
     * @var string $name
     * @return mixed|string
     */
    public function handle($shortcode, $content, $compiler, $name)
    {
        $block = $this->repository->findWhere([
            'slug' => $shortcode->alias,
        ]);
        if (!$block) {
            return null;
        }

        return $block->content;
    }
}
