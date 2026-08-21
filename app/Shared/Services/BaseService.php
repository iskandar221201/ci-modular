<?php
declare(strict_types=1);
namespace App\Shared\Services;

use App\Shared\Traits\AuditTrailTrait;
use CodeIgniter\Model;
use App\Shared\Validation\BaseValidator;
use App\Shared\Exceptions\ServiceException;
use App\Shared\Exceptions\ValidationException;
use Config\AppConstants;

abstract class BaseService
{
    use AuditTrailTrait;

    /** @var string Must be set in the child class to bind a Model. */
    protected string $modelClass;

    protected ?Model $model = null;
    protected BaseValidator $validator;

    public function __construct()
    {
        if (!empty($this->modelClass)) {
            $this->model = new $this->modelClass();
        }
        $this->validator = new BaseValidator();
    }

    protected function rules(): array
    {
        return [];
    }

    protected function messages(): array
    {
        return [];
    }

    protected function updateRules(): array
    {
        return $this->rules();
    }

    public function findAll(array $filters = [], int $perPage = AppConstants::DEFAULT_PER_PAGE): array
    {
        $search  = $filters['search'] ?? null;
        $perPage = (int) ($filters['per_page'] ?? $perPage);
        $sort    = $filters['sort'] ?? null;
        $order   = $filters['order'] ?? 'asc';

        if ($search) {
            $this->model->search($search);
        }

        $allowedOrder = ['asc', 'desc'];
        $order        = in_array(strtolower($order), $allowedOrder, true) ? strtolower($order) : 'asc';

        if ($sort !== null && $sort !== '') {
            $allowedSortFields = $this->model->allowedFields ?? [];
            if (in_array($sort, $allowedSortFields, true)) {
                $this->model->orderBy($sort, $order);
            }
        }

        if ($perPage > 0) {
            $perPage = min($perPage, AppConstants::MAX_PER_PAGE);
            $data    = $this->model->paginate($perPage);

            $lastQuery = $this->model->builder()->db()->getLastQuery();
            log_message('debug', '[BaseService::findAll] SQL: ' . $lastQuery->getOriginalQuery());
            log_message('debug', '[BaseService::findAll] Search keyword: ' . ($search ?? '(none)'));

            return [
                'data'  => $data ?? [],
                'pager' => $this->model->pager,
            ];
        }

        return [
            'data' => $this->model->findAll(AppConstants::MAX_PER_PAGE),
        ];
    }

    public function findById(int|string $id): mixed
    {
        return $this->model->find($id);
    }

    public function create(array $data): int|string
    {
        if (!empty($this->rules())) {
            $this->validate($data, $this->rules(), $this->messages());
        }

        $id = $this->model->insert($data, true);

        if ($id === false) {
            throw new ServiceException('Failed to create record', AppConstants::HTTP_SERVER_ERROR);
        }

        $this->auditCreate($id, $data);

        try {
            $this->afterCreate($id, $data);
        } catch (\Throwable $e) {
            log_message('error', '[BaseService] afterCreate hook failed: ' . $e->getMessage());
        }

        return $id;
    }

    public function update(int|string $id, array $data): mixed
    {
        $old = $this->model->find($id);

        if (!$old) {
            throw new ServiceException('Record not found', AppConstants::HTTP_NOT_FOUND);
        }

        if (!empty($this->updateRules())) {
            $this->validate($data, $this->updateRules(), $this->messages());
        }

        $result = $this->model->update($id, $data);

        if ($result === false) {
            throw new ServiceException('Failed to update record', AppConstants::HTTP_SERVER_ERROR);
        }

        $this->auditUpdate($id, (array) $old, $data);

        try {
            $this->afterUpdate($id, $data);
        } catch (\Throwable $e) {
            log_message('error', '[BaseService] afterUpdate hook failed: ' . $e->getMessage());
        }

        return $this->model->find($id);
    }

    public function delete(int|string $id): bool
    {
        $old = $this->model->find($id);

        if (!$old) {
            throw new ServiceException('Record not found', AppConstants::HTTP_NOT_FOUND);
        }

        $result = $this->model->delete($id);

        if ($result === false) {
            throw new ServiceException('Failed to delete record', AppConstants::HTTP_SERVER_ERROR);
        }

        $this->auditDelete($id, (array) $old);

        try {
            $this->afterDelete($id, (array) $old);
        } catch (\Throwable $e) {
            log_message('error', '[BaseService] afterDelete hook failed: ' . $e->getMessage());
        }

        return true;
    }

    public function validate(array $data, array $rules, array $messages = []): bool
    {
        $valid = $this->validator->validate($data, $rules, $messages);

        if ($valid === false) {
            throw new ValidationException($this->validator->getErrors());
        }

        return true;
    }

    protected function afterCreate(int|string $id, array $data): void
    {
        $this->wsPublish('create', $id, $data);
    }

    protected function afterUpdate(int|string $id, array $data): void
    {
        $this->wsPublish('update', $id, $data);
    }

    protected function afterDelete(int|string $id, array $oldData): void
    {
        $this->wsPublish('delete', $id, $oldData);
    }

    protected function getWsPayload(string $action, int|string $id, array $data): array
    {
        return ['action' => $action, 'id' => $id];
    }

    private ?string $wsChannel = null;

    private function getWsChannel(): string
    {
        if ($this->wsChannel === null) {
            $this->wsChannel = 'model:' . strtolower(
                str_replace(
                    'model',
                    '',
                    basename(str_replace('\\', '/', $this->modelClass))
                )
            );
        }

        return $this->wsChannel;
    }

    private function wsPublish(string $action, int|string $id, array $data): void
    {
        try {
            (new \App\Libraries\WsPublisher())->publish(
                $this->getWsChannel(),
                $this->getWsPayload($action, $id, $data)
            );
        } catch (\Throwable $e) {
            log_message('error', '[BaseService] wsPublish failed: ' . $e->getMessage());
        }
    }
}