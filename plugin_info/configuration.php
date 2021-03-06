<?php

/* This file is part of Jeedom.
 *
 * Jeedom is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * Jeedom is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Jeedom. If not, see <http://www.gnu.org/licenses/>.
 */

require_once dirname(__FILE__) . '/../../../core/php/core.inc.php';
include_file('core', 'authentification', 'php');
if (!isConnect()) {
    include_file('desktop', '404', 'php');
    die();
}

require_once __DIR__  . '/../core/class/jMQTTBase.class.php';
?>
<div class="eventDisplayMini"></div>
<form class="form-horizontal">
    <fieldset>
        <legend><i class="fas fa-cog"></i>{{installation}}</legend>
        <div class="form-group">
            <label class="col-sm-5 control-label">{{Installer Mosquitto localement}}</label>
            <div class="col-sm-3">
                <input id="mosquitto_por" type="checkbox" class="configKey autoCheck" data-l1key="installMosquitto"
                    checked />
            </div>
        </div>
        <legend><i class="fas fa-university"></i>{{Démons}}</legend>
        <div class="form-group">
            <label class="col-sm-5 control-label">{{Port démon python}}</label>
            <div class="col-sm-3">
                <input class="configKey form-control" data-l1key="pythonsocketport" placeholder="<?php echo jMQTTBase::get_default_python_port('jMQTT'); ?>"/>
            </div>
        </div>
        <div class="form-group">
            <label class="col-sm-5 control-label">{{Port démon websocket}}</label>
            <div class="col-sm-3">
                <input class="configKey form-control" data-l1key="websocketport" placeholder="<?php echo jMQTTBase::get_default_websocket_port('jMQTT'); ?>"/>
            </div>
        </div>
        <legend><i class="fas fa-key"></i>{{Certificats}}</legend>
        <div class="form-group">
            <label class="col-sm-5 control-label">{{Téléverser un nouveau Certificat}}</label>
            <div class="col-sm-3">
                <span class="btn btn-success btn-sm btn-file" style="position:relative;" title="Téléverser un Certificat">
                    <i class="fas fa-upload"></i><input id="mqttConfUpFile" type="file" name="file" accept=".crt, .pem, .key" data-url="plugins/jMQTT/core/ajax/jMQTT.ajax.php?action=fileupload&dir=certs">
                </span>
            </div>
        </div>
        <div class="form-group">
            <label class="col-sm-5 control-label">{{Supprimer un Certificat}}</label>
            <div class="col-sm-3">
                <select id="mqttConfDelFile" class="form-control" data-l1key="tobedeleted">
<?php
    $dir = realpath(dirname(__FILE__) . '/../' . jMQTTBase::PATH_CERTIFICATES);
    foreach (ls($dir) as $file) {
        if (in_array(strtolower(strrchr($file, '.')), array('.crt', '.key', '.pem')))
            echo str_repeat(' ', 36) . '<option value="' . $file . '">' .$file . '</option>';
    }
?>
                </select>
            </div>
            <div class="col-sm-3">
                <span class="btn btn-danger btn-sm btn-trash mqttDeleteFile" style="position:relative;margin-top: 2px;" title="Supprimer le fichier selectionné">
                    <i class="fas fa-trash"></i>
                </span>
            </div>
        </div>
        <div class="form-group"><br /></div>
    </fieldset>
</form>
<script>
$('#mqttConfUpFile').fileupload({
    dataType: 'json',
    replaceFileInput: false,
    done: function (e, data) {
        if (data.result.state != 'ok') {
            $('.eventDisplayMini').showAlert({message: data.result.result, level: 'danger'});
        } else {
            $(new Option(data.result.result, data.result.result)).appendTo('#mqttConfDelFile');
            $('#mqttConfDelFile option[value="'+data.result.result+'"]').attr('selected','selected');
            switch (data.result.result.split('.').pop()) {
                case 'crt':
                    $(new Option(data.result.result, data.result.result)).appendTo('#fTlsCaFile');
                    $('#fTlsCaFile option[value="'+data.result.result+'"]').attr('selected','selected');
                    break;
                case 'pem':
                    $(new Option(data.result.result, data.result.result)).appendTo('#fTlsClientCertFile');
                    $('#fTlsClientCertFile option[value="'+data.result.result+'"]').attr('selected','selected');
                    break;
                case 'key':
                    $(new Option(data.result.result, data.result.result)).appendTo('#fTlsClientKeyFile');
                    $('#fTlsClientKeyFile option[value="'+data.result.result+'"]').attr('selected','selected');
                    break;
            }
            $('.eventDisplayMini').showAlert({message: '{{Fichier ajouté avec succès}}', level: 'success'});
        }
        // setTimeout(function() { $('.eventDisplayMini').hideAlert(); }, 3000);
        $('#mqttConfUpFile').val(null);
    }
});

$('.mqttDeleteFile').on('click', function (){
    var oriname = $("#mqttConfDelFile").val();
    if (oriname !== null) {
        $.ajax({
            type: "POST",
            url: "plugins/jMQTT/core/ajax/jMQTT.ajax.php",
            data: {
                action: "filedelete",
                dir: "certs",
                name: oriname
            },
            global : false,
            dataType: 'json',
            error: function(request, status, error) {
                handleAjaxError(request, status, error);
            },
            success: function(data) {
                if (data.state != 'ok') {
                    $('.eventDisplayMini').showAlert({message: data.result,level: 'danger'});
                } else {
                    $("#mqttConfDelFile :selected").remove();
                    $('#fTlsCaFile option[value="'+oriname+'"]').remove(); // 3x black magic in Broker Tab
                    $('#fTlsClientCertFile option[value="'+oriname+'"]').remove();
                    $('#fTlsClientKeyFile option[value="'+oriname+'"]').remove();
                    $('.eventDisplayMini').showAlert({message: 'Suppression effectuée' ,level: 'success'});
                }
                // setTimeout(function() { $('.eventDisplayMini').hideAlert() }, 3000);
            }
        });
    }
});
</script>
