<div class="table-responsive">
    <table class="table table-bordered table-striped table-hover text-center" {{ $attributes }}>
        <thead>
            <tr>
                <th><i class="icon-energy"></i></th>
                {{ $headers }}
            </tr>
        </thead>
        <tbody>
            {{ $slot }}
        </tbody>
    </table>
</div>
