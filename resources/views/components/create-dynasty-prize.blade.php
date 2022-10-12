<div>
    <div id="create-prize" class="modal fade" wire:ignore.self role="dialog" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-bs-dismiss="modal">&times;</button>
                    <h4 class="modal-title">تعریف جوایز</h4>
                </div>
                <div class="modal-body">
                    <div>

                        @if (session()->has('success'))
                        <x-alert :type="'success'" :message="session('success')"></x-alert>
                        @endif
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group row">
                                <label class="form-col-label col-sm-6">نسبت خانوادگی</label>
                                <div class="col-sm-6">
                                    <select class="form-control rounded" wire:model="prize.member">
                                        <option selected value="">انتخاب کنید</option>
                                        <option value="father">پدر</option>
                                        <option value="mother">مادر</option>
                                        <option value="life_partner">همسر</option>
                                        <option value="sister">خواهر</option>
                                        <option value="brother">برادر</option>
                                        <option value="ofspring">فرزند</option>
                                    </select>
                                    @error('prize.member')
                                        <span class="form-text text-danger">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            <div class="form-group row">
                                <label class="form-col-label col-sm-6">افزایش سود پاداش معرفی</label>
                                <div class="col-sm-6">
                                    <input type="text" class="form-control rounded"
                                        wire:model="prize.introduction_profit_increase"
                                        onkeypress="return isNumberKey(event)">
                                        @error('prize.introduction_profit_increase')
                                        <span class="form-text text-danger">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            <div class="form-group row">
                                <label class="form-col-label col-sm-6">ذخیره سرمایه انباشته</label>
                                <div class="col-sm-6">
                                    <input type="text" class="form-control rounded"
                                        wire:model="prize.accumulated_capital_reserve"
                                        onkeypress="return isNumberKey(event)">
                                        @error('prize.accumulated_capital_reserve')
                                        <span class="form-text text-danger">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group row">
                                <label class="form-col-label col-sm-6">ذخیره دیتا</label>
                                <div class="col-sm-6">
                                    <input type="text" class="form-control rounded"
                                        wire:model="prize.data_storage" onkeypress="return isNumberKey(event)">
                                        @error('prize.data_storage')
                                        <span class="form-text text-danger">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            <div class="form-group row">
                                <label class="form-col-label col-sm-6">پاداش معرفی psc</label>
                                <div class="col-sm-6">
                                    <input type="text" class="form-control rounded" wire:model="prize.psc"
                                        onkeypress="return isNumberKey(event)">
                                        @error('prize.psc')
                                        <span class="form-text text-danger">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            <div class="form-group row">
                                <label class="form-col-label col-sm-6">رضایت</label>
                                <div class="col-sm-6">
                                    <input type="text" class="form-control rounded"
                                        wire:model="prize.satisfaction" onkeypress="return isNumberKey(event)">
                                        @error('prize.satisfaction')
                                        <span class="form-text text-danger">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="form-group row">
                        <div class="col-6">
                            <button class="btn btn-primary btn-sm btn-block round" wire:click="save">ذخیره</button>
                        </div>
                        <div class="col-6">
                            <button class="btn btn-danger btn-block round" data-bs-dismiss="modal">بستن</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
