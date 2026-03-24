<?php

namespace PhpNl\LaravelPayloadEditor\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use PhpNl\LaravelPayloadEditor\Contracts\FailedJobRepository;
use PhpNl\LaravelPayloadEditor\Engine\JobPayloadManager;

class LaravelPayloadEditorDashboard extends Component
{
    use WithPagination;

    public ?string $inspectingJobUuid = null;

    public ?object $inspectedJob = null;

    public array $schema = [];

    public array $form = [];

    public ?string $errorMessage = null;

    public function inspect(string $uuid, FailedJobRepository $repository, JobPayloadManager $manager)
    {
        $this->inspectingJobUuid = $uuid;
        $this->inspectedJob = $repository->find($uuid);

        if (! $this->inspectedJob) {
            $this->close();

            return;
        }

        try {
            $command = $manager->unserializeCommand($this->inspectedJob->payload);
            $this->schema = $manager->getEditableSchema($command);

            $this->form = [];
            foreach ($this->schema as $key => $property) {
                if ($property['editable']) {
                    $this->form[$key] = $property['value'];
                }
            }
            $this->errorMessage = null;
        } catch (\Throwable $e) {
            $this->errorMessage = 'Could not unserialize job payload. It might be internally broken or too complex for reflection: '.$e->getMessage();
            $this->schema = [];
        }
    }

    public function close()
    {
        $this->inspectingJobUuid = null;
        $this->inspectedJob = null;
        $this->schema = [];
        $this->form = [];
        $this->errorMessage = null;
    }

    public function saveAndRetry(FailedJobRepository $repository, JobPayloadManager $manager)
    {
        if (! $this->inspectedJob) {
            return;
        }

        try {
            $command = $manager->unserializeCommand($this->inspectedJob->payload);
            $newCommandString = $manager->modifyAndSerialize($command, $this->form);
            $newPayloadJson = $manager->rebuildPayload($this->inspectedJob->payload, $newCommandString);

            $repository->updatePayload($this->inspectedJob->uuid, $newPayloadJson);
            $repository->retry($this->inspectedJob->uuid);

            $this->close();
            session()->flash('success', 'Job successfully updated and queued for retry!');
        } catch (\Throwable $e) {
            $this->errorMessage = 'Failed to save and retry: '.$e->getMessage();
        }
    }

    public function render(FailedJobRepository $repository)
    {
        /** @var view-string $view */
        $view = 'laravel-payload-editor::livewire.dashboard';

        return view($view, [
            'jobs' => $repository->paginate(),
        ])->layout('laravel-payload-editor::layouts.app');
    }
}
