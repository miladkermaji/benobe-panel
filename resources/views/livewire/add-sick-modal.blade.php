<div>
    <div class="modal-header">
        <h5 class="modal-title">ثبت نوبت دستی</h5>
        <button type="button" class="btn-close" wire:click="hideModal" aria-label="Close"></button>
    </div>
    <div class="modal-body">
        <form action="" method="post">
            <input type="text" class="my-form-control-light w-100" placeholder="کدملی/کداتباع">
            <div class="mt-2">
                <a class="text-decoration-none text-primary font-bold" href="#"
                    wire:click="$dispatch('showModal', {data: {'alias': 'paziresh-modal'}})">پذیرش از مسیر ارجاع</a>
            </div>
            <div class="d-flex mt-2 gap-20">
                <button class="btn my-btn-primary w-50 h-50">تجویز نسخه</button>
                <button class="btn btn-outline-info w-50 h-50">ثبت ویزیت</button>
            </div>
        </form>
    </div>
</div>