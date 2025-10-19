<table class="form-table" role="presentation">
    <tbody>
        <tr>
            <th scope="row"><label for="user_registered">註冊時間</label></th>
            <td>
                <input name="user_registered" id="user_registered" type="text" value="<?=$user->user_registered;?>" >
            </td>
        </tr>
        <tr>
            <th scope="row"><label for="user_dob">生日</label></th>
            <td>
                <input name="user_dob" id="user_dob" type="date" value="<?=$user_dob;?>" >
            </td>
        </tr>
        <tr>
            <th scope="row"><label for="erp_uid">會員編號</label></th>
            <td>
                <input name="erp_uid" id="erp_uid" type="text" value="<?=$erp_uid;?>" >
            </td>
        </tr>
    </tbody>
</table>