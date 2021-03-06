<?php include_once("header.php"); ?>
<?php include_once("nav.php"); ?>


<section class="container-fluid" style="margin-top: 10px;">

    <div class="container wrapper">
        <div class="row">
            <div class="col-md-8">
                <div class="clearfix" style="border: 1px #ddd solid; border-radius: 5px; background: #fff; padding: 10px; margin-bottom: 10px;">
                    <h5 class="pull-left" style="">
                    <img src="libs/img/icon-title.png" style="height: 25px;"/>
                    お知らせ
                    <span class="font-color-BF2026" style="" >Resume by Category</span>
                    </h5>
                    <div class="clearfix" style="margin-top: 20px;"></div>
                    <!-- Nav tabs -->
                    <ul class="nav nav-tabs" role="tablist" style="margin-top: 20px;">
                        <li class="active"><a href="#preferredPosition" role="tab" data-toggle="tab">Preferred Position</a></li>
                        <li><a href="#fileEducation" role="tab" data-toggle="tab">File of Education</a></li>
                        <li><a href="#levelEducation" role="tab" data-toggle="tab">Level of Education</a></li>
                        <li><a href="#age" role="tab" data-toggle="tab">Age</a></li>
                        <li><a href="#region" role="tab" data-toggle="tab">Region</a></li>
                    </ul>

                    <!-- Tab panes -->
                    <div class="tab-content">
                        <div class="tab-pane fade in active" id="preferredPosition">
                            <h5 class="font-color-BF2026" style="">Preferred Positions List</h5>
                            <ul class="clearfix no-padding">
                                <?php for($i=0;$i<=30;$i++):?>
                                <li>
                                    <div class="col-md-12 no-padding">
                                        <span class="pull-left">Agriculture / Livestock / Fishery / Mining</span>
                                        <span class="pull-right font-color-BF2026">13,970</span>
                                    </div>
                                </li>
                                <?php endfor;?>
                            </ul>

                        </div>
                        <div class="tab-pane fade" id="fileEducation">...</div>
                        <div class="tab-pane fade" id="levelEducation">...</div>
                        <div class="tab-pane fade" id="age">...</div>
                        <div class="tab-pane fade" id="region">...</div>
                    </div>

                </div>

                <img src="libs/img/blank-banner-ads-01.png" style="width: 100%; height: auto;"/>
            </div>

            <?php include_once("sidebar.php"); ?>

        </div>
    </div>

</section>

<?php include_once("footer.php"); ?>