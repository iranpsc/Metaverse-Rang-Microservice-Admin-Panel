@props([
    'pageTitle' => 'عنوان صفحه'
])

@include('layouts.header')

<!-- BEGIN WRAPPER -->
<div id="wrapper">
    {{-- Sidebar --}}
    @include('layouts.sidebar')
    <!-- BEGIN PAGE CONTENT -->
    <div id="page-content">
        <div class="row">
            <div class="col-12">
                <div class="portlet box border shadow">
                    <div class="portlet-heading">
                        <div class="portlet-title">
                            <h3 class="title">
                                <i class="icon-note"></i>
                                {{ $pageTitle }}
                            </h3>
                        </div><!-- /.portlet-title -->
                        <div class="buttons-box">
                            <a class="btn btn-sm btn-default btn-round" rel="tooltip" title="آموزش" href="#video-tutorials-modal">
                                <i class="icon-question"></i>
                            </a>
                        </div><!-- /.buttons-box -->
                    </div><!-- /.portlet-heading -->
                    <div class="portlet-body">
                        {{ $slot }}
                    </div><!-- /.portlet-body -->
                </div><!-- /.portlet -->
            </div><!-- /.col-12 -->
        </div><!-- /.row -->
    </div><!-- /#page-content -->
    <!-- END PAGE CONTENT -->

</div><!-- /#wrapper -->
<!-- END WRAPPER -->

@include('layouts.footer')

