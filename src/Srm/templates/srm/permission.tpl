<section class="permission" id="srm-permission">
  <h2><a href="#permission-editor-srm" class="accordion-switcher">アプリケーション権限設定</a></h2>
  <div id="permission-editor-srm" class="accordion">
    <table>
      <thead>
        <tr>
          <td>権限適用範囲</td>
          <td>作成</td>
          <td>読取</td>
          <td>更新</td>
          <td>削除</td>
          <td>その他</td>
        </tr>
      </thead>
      <tbody>
        <tr>
          <th>帳票</th>
          <td>{% if apps.userinfo.admin == 1 or priv.receipt.create == 1 %}<input type="checkbox" value="1" name="perm[{{ filters }}srm.receipt.create]"{% if post.perm[filters ~ 'srm.receipt.create'] == 1 %} checked{% endif %}>{% else %}-{% endif %}</td>
          <td>{% if apps.userinfo.admin == 1 or priv.receipt.read   == 1 %}<input type="checkbox" value="1" name="perm[{{ filters }}srm.receipt.read]"  {% if post.perm[filters ~ 'srm.receipt.read']   == 1 %} checked{% endif %}>{% else %}-{% endif %}</td>
          <td>{% if apps.userinfo.admin == 1 or priv.receipt.update == 1 %}<input type="checkbox" value="1" name="perm[{{ filters }}srm.receipt.update]"{% if post.perm[filters ~ 'srm.receipt.update'] == 1 %} checked{% endif %}>{% else %}-{% endif %}</td>
          <td>{% if apps.userinfo.admin == 1 or priv.receipt.delete == 1 %}<input type="checkbox" value="1" name="perm[{{ filters }}srm.receipt.delete]"{% if post.perm[filters ~ 'srm.receipt.delete'] == 1 %} checked{% endif %}>{% else %}-{% endif %}</td>
          <td>{% if apps.userinfo.admin == 1 or priv.receipt.accept == 1 %}<input type="checkbox" value="1" name="perm[{{ filters }}srm.receipt.accept]"{% if post.perm[filters ~ 'srm.receipt.accept'] == 1 %} checked{% endif %}>{% else %}-{% endif %}受理</td>
        </tr>
        <tr>
          <th>取引先</th>
          <td>{% if apps.userinfo.admin == 1 or priv.client.create == 1 %}<input type="checkbox" value="1" name="perm[{{ filters }}srm.client.create]"{% if post.perm[filters ~ 'srm.client.create'] == 1 %} checked{% endif %}>{% else %}-{% endif %}</td>
          <td>{% if apps.userinfo.admin == 1 or priv.client.read   == 1 %}<input type="checkbox" value="1" name="perm[{{ filters }}srm.client.read]"  {% if post.perm[filters ~ 'srm.client.read']   == 1 %} checked{% endif %}>{% else %}-{% endif %}</td>
          <td>{% if apps.userinfo.admin == 1 or priv.client.update == 1 %}<input type="checkbox" value="1" name="perm[{{ filters }}srm.client.update]"{% if post.perm[filters ~ 'srm.client.update'] == 1 %} checked{% endif %}>{% else %}-{% endif %}</td>
          <td>{% if apps.userinfo.admin == 1 or priv.client.delete == 1 %}<input type="checkbox" value="1" name="perm[{{ filters }}srm.client.delete]"{% if post.perm[filters ~ 'srm.client.delete'] == 1 %} checked{% endif %}>{% else %}-{% endif %}</td>
          <td>-</td>
        </tr>
        <tr>
          <th>金融機関</th>
          <td>{% if apps.userinfo.admin == 1 or priv.bank.create == 1 %}<input type="checkbox" value="1" name="perm[{{ filters }}srm.bank.create]"{% if post.perm[filters ~ 'srm.bank.create'] == 1 %} checked{% endif %}>{% else %}-{% endif %}</td>
          <td>{% if apps.userinfo.admin == 1 or priv.bank.read   == 1 %}<input type="checkbox" value="1" name="perm[{{ filters }}srm.bank.read]"  {% if post.perm[filters ~ 'srm.bank.read']   == 1 %} checked{% endif %}>{% else %}-{% endif %}</td>
          <td>{% if apps.userinfo.admin == 1 or priv.bank.update == 1 %}<input type="checkbox" value="1" name="perm[{{ filters }}srm.bank.update]"{% if post.perm[filters ~ 'srm.bank.update'] == 1 %} checked{% endif %}>{% else %}-{% endif %}</td>
          <td>{% if apps.userinfo.admin == 1 or priv.bank.delete == 1 %}<input type="checkbox" value="1" name="perm[{{ filters }}srm.bank.delete]"{% if post.perm[filters ~ 'srm.bank.delete'] == 1 %} checked{% endif %}>{% else %}-{% endif %}</td>
          <td>-</td>
        </tr>
        <tr>
          <th>基本設定</th>
          <td>{% if apps.userinfo.admin == 1 or priv.template.create == 1 %}<input type="checkbox" value="1" name="perm[{{ filters }}srm.template.create]"{% if post.perm[filters ~ 'srm.template.create'] == 1 %} checked{% endif %}>{% else %}-{% endif %}</td>
          <td>{% if apps.userinfo.admin == 1 or priv.template.read   == 1 %}<input type="checkbox" value="1" name="perm[{{ filters }}srm.template.read]"  {% if post.perm[filters ~ 'srm.template.read']   == 1 %} checked{% endif %}>{% else %}-{% endif %}</td>
          <td>{% if apps.userinfo.admin == 1 or priv.template.update == 1 %}<input type="checkbox" value="1" name="perm[{{ filters }}srm.template.update]"{% if post.perm[filters ~ 'srm.template.update'] == 1 %} checked{% endif %}>{% else %}-{% endif %}</td>
          <td>{% if apps.userinfo.admin == 1 or priv.template.delete == 1 %}<input type="checkbox" value="1" name="perm[{{ filters }}srm.template.delete]"{% if post.perm[filters ~ 'srm.template.delete'] == 1 %} checked{% endif %}>{% else %}-{% endif %}</td>
          <td>-</td>
        </tr>
      </tbody>
    </table>
  </div>
</section>
