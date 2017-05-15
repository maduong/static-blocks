<?php namespace Edutalk\Base\StaticBlocks\Repositories\Contracts;

use Edutalk\Base\Models\Contracts\BaseModelContract;

interface StaticBlockRepositoryContract
{
    /**
     * @param array $data
     * @return int
     */
    public function createStaticBlock(array $data);

    /**
     * @param int|null|BaseModelContract $id
     * @param array $data
     * @return int
     */
    public function createOrUpdateStaticBlock($id, array $data);

    /**
     * @param int|null|BaseModelContract $id
     * @param array $data
     * @return int
     */
    public function updateStaticBlock($id, array $data);

    /**
     * @param int|BaseModelContract|array $id
     * @return bool
     */
    public function deleteStaticBlock($id);
}
