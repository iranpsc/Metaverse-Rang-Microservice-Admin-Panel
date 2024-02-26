<script>
    window.addEventListener('livewire:load', function() {
        var textBox = document.getElementById('content');
        var content = textBox ? CKEDITOR.replace('content') : null;
        var saveBtn = document.getElementById('store-btn');
        var deleteBtns = document.querySelectorAll('[id^=delete-btn-]');
        var editBtns = document.querySelectorAll('[id^=edit-btn-]');
        let id = null;
        let action = 'store';

        var modal = new bootstrap.Modal(document.getElementById('modal'), {
            keyboard: false
        });

        $('#modal').on('hidden.bs.modal', function() {
            @this.call('resetForm');
        });

        const Toast = Swal.mixin({
            toast: true,
            position: 'top-right',
            showConfirmButton: false,
            showCloseButton: true,
            timer: 3000,
            timerProgressBar: true,
            didOpen: (toast) => {
                toast.addEventListener('mouseenter', Swal.stopTimer)
                toast.addEventListener('mouseleave', Swal.resumeTimer)
            }
        })

        if (content) {
            CKEDITOR.editorConfig = function(config) {
                config.language = 'fa';
                config.uiColor = '#F7B42C';
                config.height = 300;
                config.toolbarCanCollapse = true;
            };
        }

        window.addEventListener('openCreateModal', event => {
            modal.show();
            if (content) content.setData('');
        });

        window.addEventListener('closeCreateModal', event => {
            modal.hide();
            if (content) content.setData('');
            showSwalNotification();
        });

        window.addEventListener('openEditModal', event => {
            action = 'update';
            id = event.detail.id;
            if (content) content.setData(event.detail.content);
            modal.show();
        });

        window.addEventListener('closeEditModal', event => {
            modal.hide();
            if (content) content.setData('');
            action = 'store';
            showSwalNotification();
        });

        saveBtn.addEventListener('click', function() {
            if (content) @this.set('content', content.getData());

            if (action == 'store') {
                @this.call('store');
            } else if (action == 'update') {
                @this.call('update', id);
            }
        });

        deleteBtns.forEach(function(btn) {
            btn.addEventListener('click', function() {
                Swal.fire({
                    title: 'آیا مطمئن هستید؟',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'بله',
                    cancelButtonText: 'خیر'
                }).then((result) => {
                    if (result.isConfirmed) {
                        var id = btn.id.split('-')[2];
                        @this.call('delete', id);
                        showSwalNotification();
                    }
                });
            });
        });

        editBtns.forEach(function(btn) {
            btn.addEventListener('click', function() {
                var id = btn.id.split('-')[2];
                @this.call('edit', id);
            });
        });

        function showSwalNotification() {
            Toast.fire({
                icon: 'success',
                title: 'عملیات موفقیت آمیز بود.'
            })
        }
    })
</script>
