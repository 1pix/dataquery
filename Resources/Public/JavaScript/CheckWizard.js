/**
 * This file contains JavaScript functions related to the dataquery SQL check wizard.
 *
 * @author Francois Suter (Cobweb) <typo3@cobweb.ch>
 * @author Fabien Udriot <fabien.udriot@ecodev.ch>
 * @package TYPO3
 * @subpackage tx_dataquery
 */

Ext.onReady(function(){

	// Container that includes widgets for validating the query
	new Ext.Container({
        renderTo: 'tx_dataquery_wizardContainer',
		items: [
			{
				xtype: 'button',
				text: TX_DATAQUERY.labels.validateButton,
				style: {
					 marginBottom: '10px'
				},
				handler: function() {
					var textarea = Ext.get(TX_DATAQUERY.fieldId);

					// Basic request in Ext
					Ext.Ajax.request({
						url: TYPO3.settings.ajaxUrls['dataquery::validate'],
						method: 'post',
						params: {
							query: textarea.dom.value
						},
						success: function(result){
							Ext.get('t3-box-result').update(result.responseText);
						}
					});
				}
			},
			{
				xtype: 'box',
				id: 't3-box-result',
				html: ''
			}
		]
	});
});
