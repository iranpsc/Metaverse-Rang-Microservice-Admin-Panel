<div>
    <div id="edit-prize-{{ $prize->id }}" class="modal fade" wire:ignore.self role="dialog" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-bs-dismiss="modal">&times;</button>
                    <h4 class="modal-title">ویرایش پاداش</h4>
                </div>
                <div class="modal-body">
                    <div>
                        @if (session('success'))
                            <x-alert :type="'success'" :message="session('success')"></x-alert>
                        @endif
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group row">
                                <label class="form-col-label col-sm-6">افزایش سود پاداش معرفی</label>
                                <div class="col-sm-6">
                                    <input type="text" class="form-control rounded"
                                        wire:model="introduction_profit_increase"
                                        value="{{ $prize->introduction_profit_increase }}"
                                        onkeypress="return isNumberKey(event)">
                                    @error("introduction_profit_increase")
                                        <span class="text-danger">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            <div class="form-group row">
                                <label class="form-col-label col-sm-6">ذخیره سرمایه انباشته</label>
                                <div class="col-sm-6">
                                    <input type="text" class="form-control rounded"
                                        value="{{ $prize->accumulated_capital_reserve }}"
                                        wire:model="accumulated_capital_reserve" onkeypress="return isNumberKey(event)">
                                        @error("accumulated_capital_reserve")
                                        <span class="text-danger">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            <div class="form-group row">
                                <label class="form-col-label col-sm-6">ذخیره دیتا</label>
                                <div class="col-sm-6">
                                    <input type="text" class="form-control rounded"
                                        value="{{ $prize->data_storage }}" wire:model="data_storage"
                                        onkeypress="return isNumberKey(event)">
                                        @error("data_storage")
                                        <span class="text-danger">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group row">
                                <label class="form-col-label col-sm-6">پاداش معرفی psc</label>
                                <div class="col-sm-6">
                                    <input type="text" class="form-control rounded" value="{{ $prize->psc }}"
                                        wire:model="psc" onkeypress="return isNumberKey(event)">
                                        @error("psc")
                                        <span class="text-danger">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            <div class="form-group row">
                                <label class="form-col-label col-sm-6">رضایت</label>
                                <div class="col-sm-6">
                                    <input type="text" class="form-control rounded"
                                        value="{{ $prize->satisfaction }}" wire:model="satisfaction"
                                        onkeypress="return isNumberKey(event)">
                                        @error("satisfaction")
                                        <span class="text-danger">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>

                        </div>
                    </div>
                </div>
                <div class="form-group row">
                    <div class="col-6">
                        <button class="btn btn-primary btn-sm w-50 round"
                            wire:click="update({{ $prize->id }})">ذخیره</button>
                    </div>
                    <div class="col-6">
                        <button class="btn btn-danger w-50 round" data-bs-dismiss="modal">بستن</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
