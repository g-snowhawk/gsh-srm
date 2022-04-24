<li id="srm-receipt"><a href="?mode=srm.receipt.response">帳票管理</a>
  {% for item in apps.receipts %}
    {% if loop.first %}
      <ul>
    {% endif %}
    <li{% if item.active == '1' %} class="active"{% endif %}><a href="?mode=srm.receipt.response&amp;t={{ item.id }}">{{ item.title }}</a></li>
    {% if loop.last %}
      </ul>
    {% endif %}
  {% endfor %}
</li>
{% if apps.hasPermission('srm.client.read') %}
  <li id="srm-client"><a href="?mode=srm.client.response">取引先</a></li>
{% endif %}
{% if apps.hasPermission('srm.bank.read') %}
  <li id="srm-bank"><a href="?mode=srm.bank.response">金融機関</a></li>
{% endif %}
{% if apps.hasPermission('srm.template.read') %}
  <li id="srm-template"><a href="?mode=srm.template.response">帳票基本設定</a></li>
{% endif %}
