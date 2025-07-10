<?php

namespace App\Livewire\Dr\Panel\SubUsers;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\SubUser;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class SubUserList extends Component
{
    use WithPagination;

    protected $paginationTheme = 'bootstrap';

    public $perPage = 20;
    public $search = '';
    public $readyToLoad = false;
    public $subUserId = null;
    public $user_id = null;
    public $mode = 'add'; // 'add' or 'edit'
    public $users = [];
    public $userSearch = '';
    public $userPage = 1;
    public $userTotal = 0;
    public $userPerPage = 50;
    public $errorMessage = '';
    public $successMessage = '';

    protected $queryString = [
        'search' => ['except' => ''],
    ];

    protected $rules = [
        'user_id' => 'required|exists:users,id',
    ];

    public function mount()
    {
        $this->perPage = max($this->perPage, 1);
    }

    public function loadSubUsers()
    {
        $this->readyToLoad = true;
    }

    public function updatedSearch()
    {
        $this->resetPage();
    }

    public function updatedUserSearch()
    {
        $this->userPage = 1;
        $this->loadUsers();
    }

    public function loadUsers()
    {
        $doctorId = Auth::guard('doctor')->id();
        $query = User::query();
        if ($this->userSearch) {
            $query->where(function ($q) {
                $q->where('first_name', 'like', '%' . $this->userSearch . '%')
                  ->orWhere('last_name', 'like', '%' . $this->userSearch . '%')
                  ->orWhere('national_code', 'like', '%' . $this->userSearch . '%')
                  ->orWhere('mobile', 'like', '%' . $this->userSearch . '%');
            });
        }
        $this->userTotal = $query->count();
        $this->users = $query->orderByDesc('id')
            ->skip(($this->userPage - 1) * $this->userPerPage)
            ->take($this->userPerPage)
            ->get();
    }

    public function nextUserPage()
    {
        if ($this->userPage * $this->userPerPage < $this->userTotal) {
            $this->userPage++;
            $this->loadUsers();
        }
    }

    public function prevUserPage()
    {
        if ($this->userPage > 1) {
            $this->userPage--;
            $this->loadUsers();
        }
    }

    public function addSubUser()
    {
        $this->validate();
        $doctorId = Auth::guard('doctor')->id();
        $exists = SubUser::where('doctor_id', $doctorId)
            ->where('subuserable_id', $this->user_id)
            ->where('subuserable_type', User::class)
            ->exists();
        if ($exists) {
            $this->errorMessage = 'این کاربر قبلاً اضافه شده است!';
            return;
        }
        SubUser::create([
            'doctor_id' => $doctorId,
            'subuserable_id' => $this->user_id,
            'subuserable_type' => User::class,
        ]);
        $this->resetForm();
        $this->successMessage = 'کاربر زیرمجموعه با موفقیت اضافه شد!';
    }

    public function editSubUser($id)
    {
        $subUser = SubUser::findOrFail($id);
        $this->subUserId = $subUser->id;
        $this->user_id = $subUser->subuserable_id;
        $this->mode = 'edit';
        $this->loadUsers();
    }

    public function updateSubUser()
    {
        $this->validate();
        $subUser = SubUser::findOrFail($this->subUserId);
        if ($subUser->subuserable_id == $this->user_id && $subUser->subuserable_type == User::class) {
            $this->successMessage = 'بدون تغییر! مقدار جدید همان مقدار قبلی است.';
            return;
        }
        $exists = SubUser::where('doctor_id', $subUser->doctor_id)
            ->where('subuserable_id', $this->user_id)
            ->where('subuserable_type', User::class)
            ->exists();
        if ($exists) {
            $this->errorMessage = 'این کاربر قبلاً به لیست اضافه شده است!';
            return;
        }
        $subUser->subuserable_id = $this->user_id;
        $subUser->subuserable_type = User::class;
        $subUser->save();
        $this->resetForm();
        $this->successMessage = 'کاربر زیرمجموعه با موفقیت ویرایش شد!';
    }

    public function deleteSubUser($id)
    {
        $subUser = SubUser::findOrFail($id);
        $subUser->delete();
        $this->successMessage = 'کاربر زیرمجموعه حذف شد!';
    }

    public function resetForm()
    {
        $this->subUserId = null;
        $this->user_id = null;
        $this->mode = 'add';
        $this->errorMessage = '';
        $this->successMessage = '';
        $this->userSearch = '';
        $this->userPage = 1;
        $this->loadUsers();
    }

    private function getSubUsersQuery()
    {
        $doctorId = Auth::guard('doctor')->id();
        return SubUser::where('doctor_id', $doctorId)
            ->whereHasMorph('subuserable', [User::class], function ($q) {
                $q->where(function ($query) {
                    $query->where('first_name', 'like', '%' . $this->search . '%')
                        ->orWhere('last_name', 'like', '%' . $this->search . '%')
                        ->orWhere('national_code', 'like', '%' . $this->search . '%')
                        ->orWhere('mobile', 'like', '%' . $this->search . '%');
                });
            })
            ->with('subuserable')
            ->orderByDesc('id');
    }

    public function render()
    {
        $items = $this->readyToLoad ? $this->getSubUsersQuery()->paginate($this->perPage) : null;
        return view('livewire.dr.panel.sub-users.sub-user-list', [
            'subUsers' => $items,
            'users' => $this->users,
            'userTotal' => $this->userTotal,
            'userPage' => $this->userPage,
            'userPerPage' => $this->userPerPage,
        ]);
    }
}
