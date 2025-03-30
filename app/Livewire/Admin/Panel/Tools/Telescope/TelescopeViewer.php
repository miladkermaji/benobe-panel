<?php

namespace App\Livewire\Admin\Panel\Tools\Telescope;

use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Livewire\WithPagination;

class TelescopeViewer extends Component
{
    use WithPagination;

    protected $paginationTheme = 'bootstrap';

    public $perPage     = 10;
    public $search      = '';
    public $type        = 'all'; // نوع ورودی (request, query, log, exception, ...)
    public $readyToLoad = false;

    protected $queryString = [
        'search' => ['except' => ''],
        'type'   => ['except' => 'all'],
    ];

    public function mount()
    {
        $this->perPage = max($this->perPage, 1);
    }

    public function loadEntries()
    {
        $this->readyToLoad = true;
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingType()
    {
        $this->resetPage();
    }

    public function render()
    {
        $entries = $this->readyToLoad
        ? DB::table('telescope_entries')
            ->join('telescope_entries_tags', 'telescope_entries.uuid', '=', 'telescope_entries_tags.entry_uuid')
            ->where(function ($query) {
                if ($this->type !== 'all') {
                    $query->where('telescope_entries.type', $this->type);
                }
                if ($this->search) {
                    $query->where('telescope_entries.content', 'like', '%' . $this->search . '%');
                }
            })
            ->select('telescope_entries.*')
            ->orderBy('telescope_entries.created_at', 'desc')
            ->paginate($this->perPage)
        : null;

        return view('livewire.admin.panel.tools.telescope.telescope-viewer', [
            'entries' => $entries,
        ]);
    }
}
