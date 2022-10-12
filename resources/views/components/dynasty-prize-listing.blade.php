    <div id="view-prize-{{ $prize->id }}" class="modal fade" role="dialog" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-bs-dismiss="modal">&times;</button>
                    <h4 class="modal-title">جزپیات پاداش</h4>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group row">
                                <label class="form-col-label col-sm-6">نسبت خانوادگی</label>
                                <div class="col-sm-6">
                                    <input type="text" class="form-control rounded" disabled
                                        value="{{ \App\Helpers\getRelationTitle($prize->member) }}">
                                </div>
                            </div>
                            <div class="form-group row">
                                <label class="form-col-label col-sm-6">افزایش سود پاداش معرفی</label>
                                <div class="col-sm-6">
                                    <input type="text" class="form-control rounded" disabled
                                        value="{{ $prize->introduction_profit_increase }}">
                                </div>
                            </div>
                            <div class="form-group row">
                                <label class="form-col-label col-sm-6">ذخیره سرمایه انباشته</label>
                                <div class="col-sm-6">
                                    <input type="text" class="form-control rounded" disabled
                                        value="{{ $prize->accumulated_capital_reserve }}">
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group row">
                                <label class="form-col-label col-sm-6">ذخیره دیتا</label>
                                <div class="col-sm-6">
                                    <input type="text" class="form-control rounded" disabled
                                        value="{{ $prize->data_storage }}">
                                </div>
                            </div>
                            <div class="form-group row">
                                <label class="form-col-label col-sm-6">پاداش معرفی psc</label>
                                <div class="col-sm-6">
                                    <input type="text" class="form-control rounded" disabled
                                        value="{{ $prize->psc }}">
                                </div>
                            </div>
                            <div class="form-group row">
                                <label class="form-col-label col-sm-6">رضایت</label>
                                <div class="col-sm-6">
                                    <input type="text" class="form-control rounded" disabled
                                        value="{{ $prize->satisfaction }}">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-info w-25 mx-auto round" data-bs-dismiss="modal">بستن</button>
                </div>
            </div>
        </div>
    </div>
