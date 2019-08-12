
var required_index=2;
var other_persons=1;
var other_address=1;
var collector_index=1;
var custom_field=0;
$(document).ready(function () {

    $customBlockShow = $('.custom-field-block-show');
    $customBlockPlace = $('.custom-field-place');
    $addFieldButton = $('.add-custom-field-button');
    $customFieldPlace = $('.custom-field-place');
    $customFieldPlace.css({
        'display':'none'
    })

    $addCustonField = $('.add-custom-field');
    $dropDownBlcok = $('.modal-dropdown');
    $dropDownBlcok.css({
        'display': 'none'
    })



//custom files

    $addFile = $('.add-custom-file');
    $deleteFile = $('.delete-custom-file');
    $deleteFile.css({
        'display':'none'
    })
    var fileName ="";
    if($('.required_item_index').val()!=null)
    {
        required_index=parseInt($('.required_item_index').val())+1;
    }

    $addFile.click(function () {
        var fileName = $(this).next().val();

        if(fileName.length != 0) {

            $(this).prev().append('<div class = "addedParent">' +
                '                                    <p class="addedText_p">' + fileName + '</p>\n' +
                '                                    <div class="form-group row file-input added">\n' +
                '                                        <div class="files-div-content">\n' +
                '                                            <div class="media-body">\n' +
                '                                                    <input type="hidden" role="uploadcare-uploader" name="required_files[file-directory]['+required_index+']" >\n' +
                '                                                </div>\n' +
                '                                                <input type="hidden" value="'+fileName+'" name="required_files[required-file-title]['+required_index+']">\n' +
                '                                            <button id ="delete-custom-file-" style="margin-top:10px" class="btn delete-custom-file" onclick ="f1(this)" type="button"><i class="fa fa-minus" aria-hidden="true"></i></button>\n'+
                '                                        </div>\n' +
                '                                    </div></div>');


            required_index++;
        }else{
            $(this).next().attr("placeholder", "Must Fill This*");
        }
    })

//end


    $addFieldButton.click(function () {
        $customFieldPlace.css({
            'display':'block'
        })
    })


    $addcustomblock = $('.add-custom-block');
    $custfield = $('.custom-field');
    $custOpener =$('.custom-fields-opener');


    $custfield.css({
        'display':'none'
    })

    $cf = $('.custom-fields-opener');
    $cf.css({
        'display':'none'
    })


    $('.add-custom-block').click(function () {
        $(this).parents('.modal-content ').children('.modal-body').children('.custom-fields-opener').css({
            'display':'block',
        })


    })

    $('.remove-custom-field').click(function () {
        $(this).parents('.modal-content ').children('.modal-body').children('.custom-fields-opener').css({
            'display':'none',
        })
    })


    $addCustomField = $('.add-custom-field-button');
    $deleteCustomField = $('.delete-custom-field-button');
    $customFieldPlace = $('.adding-place');
    $deleteCustomField.css({
        'display':'none'
    })
    if($('.custom_field_index_1').val()!=null)
    {
        custom_field=parseInt($('.custom_field_index_1').val())+1;
    }
    $addCustomField.click(function () {




        $(this).parents('.modal-content ').children('.modal-body').children('.custom-fields-opener').next().prepend('<div class="form-group row customField">\n' +
            '                                    <div class="col-12">\n' +
            '                                        <p class = "custom-field-to-attach-text">Title & Description</p>\n'+
            '                                        <textarea class="form-control"  name="custom_fields[custom-field-des]['+custom_field+']" id="custom-textarea" placeholder="add text"></textarea>\n' +
            '                                        <p class = "custom-field-to-attach-text">Files To Attach</p>\n' +
            '                                        <div class="media-body">\n' +
            '                                                    <input type="hidden" role="uploadcare-uploader" name="custom_fields[file-directory]['+custom_field+']" >\n' +
            '                                            </div>\n' +
            '                                        <button class="btn delete-custom-field-button" style="margin-top: 10px" onclick="f2(this)" type="button"><i class="fa fa-minus" aria-hidden="true"></i></button>                                    ' +
            '</div>\n' +
            '</div>');
        custom_field++;

    })

    /////tables
    $addTr = $('.add-table-tr');
    $delTr = $('.delete-table-tr');
    if($('.other_person_index').val()!=null)
    {
        other_persons=parseInt($('.other_person_index').val())+1;
    }

    $addTr.click(function () {

        $(this).parents('.custom-collapse').children('.other-persons-table').append('<tr class="tr0">\n' +
            '                                        <td><input class="form-control" type="text" name="other_person_name['+other_persons+'][name]"  maxlength="64" ></td>\n' +
            '                                        <td><input class="form-control" type="text" name="other_person_address['+other_persons+'][address]"   maxlength="64" ></td>\n' +
            '                                        <td><input class="form-control" type="text" name="other_person_relationship['+other_persons+'][relationship]"  maxlength="64" ></td>\n' +
            '                                        <td>\n' +
            '                                            <ul style="list-style: none;margin-left: 0;padding-left: 0;font-size: 10px">\n' +
            '                                                <li>HOME:<input class="form-control little-input"   type="text"  name="other_person_telephone['+other_persons+'][home]" maxlength="64" ></li>\n' +
            '                                                <li>WORK:<input class="form-control little-input"   type="text"  name="other_person_telephone['+other_persons+'][work]" maxlength="64" ></li>\n' +
            '                                                <li>MOBILE:<input class="form-control little-input" type="text"  name="other_person_telephone['+other_persons+'][mobile]" maxlength="64" ></li>\n' +
            '                                            </ul>\n' +
            '                                        </td>\n' +
            '                                    </tr>');

        other_persons++;

    })
    $delTr.click(function () {
        $(this).parents('.custom-collapse').children('.other-persons-table').children().children('.tr0').last().remove();
    })

    $addTr2 = $('.add-second-table-tr');
    $delTr2 = $('.delete-second-table-tr');
    if($('.collector_index_1').val()!=null)
    {
        collector_index=parseInt($('.collector_index_1').val())+1;
    }
    $addTr2.click(function () {



        $(this).parents('.custom-collapse').children('.collectors-table').append( '<tr class="tr0">\n'+
            '                                        <td><input class="form-control" type="text" name="collector_name['+collector_index+'][name]" maxlength="64" ></td>\n' +
            '                                        <td><input class="form-control" type="text" name="collector_address['+collector_index+'][address]"   maxlength="64" ></td>\n' +
            '                                        <td><input class="form-control" type="text" name="collector_relationship['+collector_index+'][relationship]" maxlength="64" ></td>\n' +
            '                                        <td>\n' +
            '                                            <ul style="list-style: none;margin-left: 0;padding-left: 0;font-size: 10px">\n' +
            '                                                <li>HOME:<input class="form-control little-input"   type="text" name="collector_telephone['+collector_index+'][home]"   maxlength="64" ></li>\n' +
            '                                                <li>WORK:<input class="form-control little-input"   type="text" name="collector_telephone['+collector_index+'][work]"   maxlength="64" ></li>\n' +
            '                                                <li>MOBILE:<input class="form-control little-input" type="text" name="collector_telephone['+collector_index+'][mobile]" maxlength="64" ></li>\n' +
            '                                            </ul>\n' +
            '                                        </td>\n' +
            '                                    </tr>');

        collector_index++;
    })

    $delTr2.click(function () {
        $(this).parents('.custom-collapse').children('.collectors-table').children().children('.tr0').last().remove();
    })
    //End Table


    //Other Parent address
    $addAdd = $('.add-address-tr');
    $delAdd = $('.delete-address-tr');
    if($('.other_address_index').val()!=null)
    {
        other_address=parseInt($('.other_address_index').val())+1;
    }

    $addAdd.click(function () {

        $(this).parents('.custom-collapse').children('.other-address-table').append(
            '                           <div class="ad0">\n' +
            '                               <hr>\n' +
            '                               <br>\n' +
            '                               <h5>Other Address '+other_address+'</h5>\n' +
            '                                   <div class="form-group row">\n' +
            '                                   <label class="col-md-4 col-form-label" for="other_street">Name Address:</label>\n' +
            '                                    <div class="col-md-8">\n' +
            '                                        <input class="form-control" type="text" name="other_address['+other_address+'][name]" maxlength="64">\n' +
            '                                  </div>\n' +
            '                                </div>\n' +
            '                                <div class="form-group row">\n' +
            '                                    <label class="col-md-4 col-form-label" for="other_street">Street Address:</label>\n' +
            '                                   <div class="col-md-8">\n' +
            '                                        <input class="form-control" type="text" name="other_address['+other_address+'][address]" maxlength="64">\n' +
            '                                  </div>\n' +
            '                               </div>\n' +
            '                                <div class="form-group row">\n' +
            '                                   <label class="col-md-4 col-form-label" for="other_city">City/Town:</label>\n' +
            '                                   <div class="col-md-8">\n' +
            '                                       <input class="form-control" type="text" name="other_address['+other_address+'][city]" maxlength="32">\n' +
            '                                   </div>\n' +
            '                               </div>\n' +
            '                               <div class="form-group row">\n' +
            '                                   <label class="col-md-4 col-form-label" for="other_postal_code">Postal Code:</label>\n' +
            '                                   <div class="col-md-8">\n' +
            '                                       <input class="form-control" type="text" name="other_address['+other_address+'][postal]" maxlength="9" pattern="([a-zA-Z0-9]{1,5}(\s|\-|)[a-zA-Z0-9]{1,4})">\n' +
            '                                   </div>\n' +
            '                               </div>\n' +
            '                               <div class="form-group row">\n' +
            '                                   <label class="col-md-4 col-form-label" for="other_phone">Phone:</label>\n' +
            '                                   <div class="col-md-8">\n' +
            '                                       <input class="form-control" type="text" name="other_address['+other_address+'][phone]" maxlength="32" pattern="(((\+|00)|)(.\d{1,12}))">\n' +
            '                                   </div>\n' +
            '                               </div>' +
            '                               <br>' +
            '                           </div>');

        other_address++;

    })
    $delAdd.click(function () {
        $(this).parents('.custom-collapse').children('.other-address-table').children('.ad0').last().remove();
    })
    //End other parent address


    $('.opened-button').css({
        'display':'block'
    })

    if($('#enrollmentCounter').length){

        $.ajax({
            url: "/family-and-child/get-enrollment-count",
            type: "POST",
            success: function(data){

                $('#enrollmentCounter').html(data);

            }
        });

    }


})


function f1(a) {
    $(a).parents('.addedParent').remove()
}
function f2(a) {
    $(a).parents('.customField').remove()
}


