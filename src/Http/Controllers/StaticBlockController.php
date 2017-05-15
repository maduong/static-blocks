<?php namespace Edutalk\Base\StaticBlocks\Http\Controllers;

use Illuminate\Http\Request;
use Edutalk\Base\Http\Controllers\BaseAdminController;
use Edutalk\Base\Http\DataTables\AbstractDataTables;
use Edutalk\Base\Repositories\Eloquent\EloquentBaseRepository;
use Edutalk\Base\StaticBlocks\Http\DataTables\StaticBlockDataTable;
use Edutalk\Base\StaticBlocks\Http\Requests\CreateStaticBlockRequest;
use Edutalk\Base\StaticBlocks\Http\Requests\UpdateStaticBlockRequest;
use Edutalk\Base\StaticBlocks\Repositories\Contracts\StaticBlockRepositoryContract;
use Edutalk\Base\StaticBlocks\Repositories\StaticBlockRepository;
use Yajra\Datatables\Engines\BaseEngine;

class StaticBlockController extends BaseAdminController
{
    protected $module = EDUTALK_STATIC_BLOCKS;

    /**
     * @var StaticBlockRepository|EloquentBaseRepository
     */
    protected $repository;

    public function __construct(StaticBlockRepositoryContract $repository)
    {
        parent::__construct();

        $this->repository = $repository;

        $this->middleware(function (Request $request, $next) {
            $this->getDashboardMenu($this->module);

            $this->breadcrumbs->addLink(trans('edutalk-static-blocks::base.page_title'), route('admin::static-blocks.index.get'));

            return $next($request);
        });
    }

    /**
     * @param AbstractDataTables|BaseEngine $dataTables
     * @return @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function getIndex(StaticBlockDataTable $dataTables)
    {
        $this->setPageTitle(trans('edutalk-static-blocks::base.page_title'));

        $this->dis['dataTable'] = $dataTables->run();

        return do_filter(BASE_FILTER_CONTROLLER, $this, EDUTALK_STATIC_BLOCKS, 'index.get', $dataTables)->viewAdmin('index');
    }

    /**
     * @param AbstractDataTables|BaseEngine $dataTables
     * @return mixed
     */
    public function postListing(StaticBlockDataTable $dataTables)
    {
        $data = $dataTables->with($this->groupAction());

        return do_filter(BASE_FILTER_CONTROLLER, $data, EDUTALK_STATIC_BLOCKS, 'index.post', $this);
    }

    /**
     * Handle group actions
     * @return array
     */
    protected function groupAction()
    {
        $data = [];
        if ($this->request->get('customActionType', null) === 'group_action') {
            if (!$this->userRepository->hasPermission($this->loggedInUser, ['edit-static-blocks'])) {
                return [
                    'customActionMessage' => trans('edutalk-acl::base.do_not_have_permission'),
                    'customActionStatus' => 'danger',
                ];
            }

            $ids = (array)$this->request->get('id', []);
            $actionValue = $this->request->get('customActionValue');

            switch ($actionValue) {
                case 'deleted':
                    if (!$this->userRepository->hasPermission($this->loggedInUser, ['delete-static-blocks'])) {
                        return [
                            'customActionMessage' => trans('edutalk-acl::base.do_not_have_permission'),
                            'customActionStatus' => 'danger',
                        ];
                    }
                    /**
                     * Delete items
                     */
                     $ids = do_filter(BASE_FILTER_BEFORE_DELETE, $ids, EDUTALK_STATIC_BLOCKS);

                     $result = $this->repository->delete($ids);

                     do_action(BASE_ACTION_AFTER_DELETE, EDUTALK_STATIC_BLOCKS, $ids, $result);
                    break;
                case 'activated':
                case 'disabled':
                    $result = $this->repository->updateMultiple($ids, [
                        'status' => $actionValue,
                    ]);
                    break;
                default:
                    return [
                        'customActionMessage' => trans('edutalk-core::errors.' . \Constants::METHOD_NOT_ALLOWED . '.message'),
                        'customActionStatus' => 'danger'
                    ];
                    break;
            }
            $data['customActionMessage'] = $result ? trans('edutalk-core::base.form.request_completed') : trans('edutalk-core::base.form.error_occurred');
            $data['customActionStatus'] = !$result ? 'danger' : 'success';

        }
        return $data;
    }

    /**
     * Update status
     * @param $id
     * @param $status
     * @return \Illuminate\Http\JsonResponse
     */
    public function postUpdateStatus($id, $status)
    {
        $data = [
            'status' => $status
        ];
        $result = $this->repository->update($id, $data);
        $msg = $result ? trans('edutalk-core::base.form.request_completed') : trans('edutalk-core::base.form.error_occurred');
        $code = $result ? \Constants::SUCCESS_NO_CONTENT_CODE : \Constants::ERROR_CODE;
        return response()->json(response_with_messages($msg, !$result, $code), $code);
    }

    /**
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function getCreate()
    {
        do_action(BASE_ACTION_BEFORE_CREATE, EDUTALK_STATIC_BLOCKS, 'create.get');

        $this->assets
            ->addJavascripts([
                'jquery-ckeditor'
            ]);

        $this->setPageTitle(trans('edutalk-static-blocks::base.form.create'));
        $this->breadcrumbs->addLink(trans('edutalk-static-blocks::base.form.create'));

        return do_filter(BASE_FILTER_CONTROLLER, $this, EDUTALK_STATIC_BLOCKS, 'create.get')->viewAdmin('create');
    }

    public function postCreate(CreateStaticBlockRequest $request)
    {
        do_action(BASE_ACTION_BEFORE_CREATE, EDUTALK_STATIC_BLOCKS, 'create.post');

        $data = $this->parseData($request);
        $data['created_by'] = $this->loggedInUser->id;

        $result = $this->repository->createStaticBlock($data);

        do_action(BASE_ACTION_AFTER_CREATE, EDUTALK_STATIC_BLOCKS, $result);

        $msgType = !$result ? 'danger' : 'success';
        $msg = $result ? trans('edutalk-core::base.form.request_completed') : trans('edutalk-core::base.form.error_occurred');

        flash_messages()
            ->addMessages($msg, $msgType)
            ->showMessagesOnSession();

        if (!$result) {
            return redirect()->back()->withInput();
        }

        if ($this->request->has('_continue_edit')) {
            return redirect()->to(route('admin::static-blocks.edit.get', ['id' => $result]));
        }

        return redirect()->to(route('admin::static-blocks.index.get'));
    }

    /**
     * @param $id
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\Http\RedirectResponse|\Illuminate\View\View
     */
    public function getEdit($id)
    {
        $item = $this->repository->find($id);

        if (!$item) {
            flash_messages()
                ->addMessages(trans('edutalk-core::base.item_not_exists'), 'danger')
                ->showMessagesOnSession();

            return redirect()->back();
        }

        $item = do_filter(BASE_FILTER_BEFORE_UPDATE, $item, EDUTALK_STATIC_BLOCKS, 'edit.get');

        $this->assets
            ->addJavascripts([
                'jquery-ckeditor'
            ]);

        $this->setPageTitle(trans('edutalk-static-blocks::base.form.edit_item') . ' #' . $item->id);
        $this->breadcrumbs->addLink(trans('edutalk-static-blocks::base.form.edit_item'));

        $this->dis['object'] = $item;

        return do_filter(BASE_FILTER_CONTROLLER, $this, EDUTALK_STATIC_BLOCKS, 'edit.get', $id)->viewAdmin('edit');
    }

    /**
     * @param $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function postEdit(UpdateStaticBlockRequest $request, $id)
    {
        $item = $this->repository->find($id);

        if (!$item) {
            flash_messages()
                ->addMessages(trans('edutalk-core::base.item_not_exists'), 'danger')
                ->showMessagesOnSession();

            return redirect()->back();
        }

        $item = do_filter(BASE_FILTER_BEFORE_UPDATE, $item, EDUTALK_STATIC_BLOCKS, 'edit.post');

        $data = $this->parseData($request);
        $data['updated_by'] = $this->loggedInUser->id;

        $result = $this->repository->updateStaticBlock($item, $data);

        do_action(BASE_ACTION_AFTER_UPDATE, EDUTALK_STATIC_BLOCKS, $id, $result);

        $msgType = !$result ? 'danger' : 'success';
        $msg = $result ? trans('edutalk-core::base.form.request_completed') : trans('edutalk-core::base.form.error_occurred');

        flash_messages()
            ->addMessages($msg, $msgType)
            ->showMessagesOnSession();

        if ($this->request->has('_continue_edit')) {
            return redirect()->back();
        }

        return redirect()->to(route('admin::static-blocks.index.get'));
    }

    /**
     * @param $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function deleteDelete($id)
    {
        $id = do_filter(BASE_FILTER_BEFORE_DELETE, $id, EDUTALK_STATIC_BLOCKS);

        $result = $this->repository->deleteStaticBlock($id);

        do_action(BASE_ACTION_AFTER_DELETE, EDUTALK_STATIC_BLOCKS, $id, $result);

        $msg = $result ? trans('edutalk-core::base.form.request_completed') : trans('edutalk-core::base.form.error_occurred');
        $code = $result ? \Constants::SUCCESS_NO_CONTENT_CODE : \Constants::ERROR_CODE;
        return response()->json(response_with_messages($msg, !$result, $code), $code);
    }

    protected function parseData($request)
    {
        $data = $request->get('static_block', []);
        if (!$data['slug']) {
            $data['slug'] = str_slug($data['title']);
        }
        return $data;
    }
}
