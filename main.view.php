<?php include "_header.view.php" ?> 

<div class="row">
    <div class="twelve columns text-right"><p><a href="?op=logout">Disconnect</a><p></div>
</div>

<div class="row panel">
        <form action="?op=shorten" method="post" class="">
            <div class="twelve columns">
                <input class="<?php tplErr('sh_target')?>" type="text" name="target" placeholder="Shorten URL" value="<?php tpl('sh_target') ?>"/>
                <?php tplErrMsg('sh_target') ?>
            </div>

            <div class="small-4 columns">
                
                <div class="row collapse">
                    <div class="small-3 columns">
                      <span class="text-right prefix"><?=$host;?>/&nbsp;</span>
                    </div>
                    <div class="small-9 columns">
                        <input type="text" class="<?php tplErr('sh_shortcut')?>" name="shortcut" placeholder="Custom shortcut" value="<?php tpl('sh_shortcut') ?>">
                        <?php tplErrMsg('sh_shortcut') ?>
                    </div>
                  </div>        
            </div>
        
            <div class="small-8 columns text-right">
                <button type="submit">Shorten!</button>
            </div>
        </form>
</div>           
      
       
<div class="row">
    <div class="twelve columns">
            
    <h1>All URLs</h1>
    
    <table width="100%">
        <thead>
            <tr>
                <td>Shortcut</td>
                <td>Target</td>
                <td></td>
            </tr>
        </thead>    
        <tbody><?php foreach($allUrls as $url): ?>
            <tr>
                <td><a href="http://<?=$host?>/<?=shortcut_encode($url['id'])?>"><?=shortcut_encode($url['id'])?></a></td>
                <td class=""><?=htmlentities($url['target'])?></td>
                <td class="text-right"><a href="?op=delete&amp;id=<?=$url['id']?>">Delete</a></td>
            </tr>
        <?php endforeach; ?></tbody>
    </table>
    </div>
</div>
<!-- Not implemented
<div class="row">
    <div class="twelve columns">
        <h1 id="password">Change password</h1>
    </div>

    <form action="?op=password#password" method="post">

    <div class="medium-4 columns">
        <label for="current">Current password</label>
        <input type="password" name="current" id="currentw"/>
    </div>

    <div class="medium-4 columns">
        <label for="new">New password</label>
        <input type="password" name="new" id="new"/>
    </div>

    <div class="medium-4 columns">
        <label for="new">Repeat new password</label>
        <input type="password" name="new" id="new"/>
    </div>

    <div class="medium-2 columns">
        <button type="submit">Change</button>
    </div>

    </form>
</div>
-->
<?php include "_footer.view.php"?>