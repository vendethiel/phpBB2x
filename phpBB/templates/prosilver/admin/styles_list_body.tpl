				<h1>{L_STYLES_TITLE}</h1>
				<p>{L_STYLES_TEXT}</p>
				<table cellspacing="1">
				<col class="row1" /><col class="row1" /><col class="row2" />
				<thead>
				<tr>
					<th>{L_STYLE}</th>
					<th>{L_TEMPLATE}</th>
					<th>{L_EDIT}/{L_DELETE}</th>
				</tr>
				</thead>
				<tbody>
				<!-- BEGIN styles -->
				<tr>
					<td><strong>{styles.STYLE_NAME}</strong></td>
					<td style="text-align:center">{styles.TEMPLATE_NAME}</td>
					<td style="text-align:center"><a href="{styles.U_STYLES_EDIT}" title="{L_EDIT}"><img src="../templates/prosilver/admin/images/icon_edit.gif" alt="" /></a> <a href="{styles.U_STYLES_DELETE}" title="{L_DELETE}"><img src="../templates/prosilver/admin/images/icon_delete.gif" alt="" /></a></td>
				</tr>
				<!-- END styles -->
				</tbody>
				</table>