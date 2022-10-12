var wms_layers = [];


        var lyr_OpenStreetMap_0 = new ol.layer.Tile({
            'title': 'OpenStreetMap',
            'type': 'base',
            'opacity': 1.000000,
            
            
            source: new ol.source.XYZ({
    attributions: ' ',
                url: 'https://tile.openstreetmap.org/{z}/{x}/{y}.png'
            })
        });
var format_behdashtii_1 = new ol.format.GeoJSON();
var features_behdashtii_1 = format_behdashtii_1.readFeatures(json_behdashtii_1, 
            {dataProjection: 'EPSG:4326', featureProjection: 'EPSG:3857'});
var jsonSource_behdashtii_1 = new ol.source.Vector({
    attributions: ' ',
});
jsonSource_behdashtii_1.addFeatures(features_behdashtii_1);
var lyr_behdashtii_1 = new ol.layer.Vector({
                declutter: true,
                source:jsonSource_behdashtii_1,
maxResolution:1.4002233076130983,
 
                style: style_behdashtii_1,
                interactive: true,
                title: '<img src="styles/legend/behdashtii_1.png" /> behdashtii'
            });
var format_edari_2 = new ol.format.GeoJSON();
var features_edari_2 = format_edari_2.readFeatures(json_edari_2, 
            {dataProjection: 'EPSG:4326', featureProjection: 'EPSG:3857'});
var jsonSource_edari_2 = new ol.source.Vector({
    attributions: ' ',
});
jsonSource_edari_2.addFeatures(features_edari_2);
var lyr_edari_2 = new ol.layer.Vector({
                declutter: true,
                source:jsonSource_edari_2,
maxResolution:1.4002233076130983,
 
                style: style_edari_2,
                interactive: true,
                title: '<img src="styles/legend/edari_2.png" /> edari'
            });
var format_gardashgari_3 = new ol.format.GeoJSON();
var features_gardashgari_3 = format_gardashgari_3.readFeatures(json_gardashgari_3, 
            {dataProjection: 'EPSG:4326', featureProjection: 'EPSG:3857'});
var jsonSource_gardashgari_3 = new ol.source.Vector({
    attributions: ' ',
});
jsonSource_gardashgari_3.addFeatures(features_gardashgari_3);
var lyr_gardashgari_3 = new ol.layer.Vector({
                declutter: true,
                source:jsonSource_gardashgari_3,
maxResolution:1.4002233076130983,
 
                style: style_gardashgari_3,
                interactive: true,
                title: '<img src="styles/legend/gardashgari_3.png" /> gardashgari'
            });
var format_namayeshgaha_4 = new ol.format.GeoJSON();
var features_namayeshgaha_4 = format_namayeshgaha_4.readFeatures(json_namayeshgaha_4, 
            {dataProjection: 'EPSG:4326', featureProjection: 'EPSG:3857'});
var jsonSource_namayeshgaha_4 = new ol.source.Vector({
    attributions: ' ',
});
jsonSource_namayeshgaha_4.addFeatures(features_namayeshgaha_4);
var lyr_namayeshgaha_4 = new ol.layer.Vector({
                declutter: true,
                source:jsonSource_namayeshgaha_4,
maxResolution:1.4002233076130983,
 
                style: style_namayeshgaha_4,
                interactive: true,
                title: '<img src="styles/legend/namayeshgaha_4.png" /> namayeshgaha'
            });
var format_mazhabi_5 = new ol.format.GeoJSON();
var features_mazhabi_5 = format_mazhabi_5.readFeatures(json_mazhabi_5, 
            {dataProjection: 'EPSG:4326', featureProjection: 'EPSG:3857'});
var jsonSource_mazhabi_5 = new ol.source.Vector({
    attributions: ' ',
});
jsonSource_mazhabi_5.addFeatures(features_mazhabi_5);
var lyr_mazhabi_5 = new ol.layer.Vector({
                declutter: true,
                source:jsonSource_mazhabi_5,
maxResolution:1.4002233076130983,
 
                style: style_mazhabi_5,
                interactive: true,
                title: '<img src="styles/legend/mazhabi_5.png" /> mazhabi'
            });
var format_parking_6 = new ol.format.GeoJSON();
var features_parking_6 = format_parking_6.readFeatures(json_parking_6, 
            {dataProjection: 'EPSG:4326', featureProjection: 'EPSG:3857'});
var jsonSource_parking_6 = new ol.source.Vector({
    attributions: ' ',
});
jsonSource_parking_6.addFeatures(features_parking_6);
var lyr_parking_6 = new ol.layer.Vector({
                declutter: true,
                source:jsonSource_parking_6,
maxResolution:1.4002233076130983,
 
                style: style_parking_6,
                interactive: true,
                title: '<img src="styles/legend/parking_6.png" /> parking'
            });
var format_farhangi_7 = new ol.format.GeoJSON();
var features_farhangi_7 = format_farhangi_7.readFeatures(json_farhangi_7, 
            {dataProjection: 'EPSG:4326', featureProjection: 'EPSG:3857'});
var jsonSource_farhangi_7 = new ol.source.Vector({
    attributions: ' ',
});
jsonSource_farhangi_7.addFeatures(features_farhangi_7);
var lyr_farhangi_7 = new ol.layer.Vector({
                declutter: true,
                source:jsonSource_farhangi_7,
maxResolution:1.4002233076130983,
 
                style: style_farhangi_7,
                interactive: true,
                title: '<img src="styles/legend/farhangi_7.png" /> farhangi'
            });
var format_faza_sabz_8 = new ol.format.GeoJSON();
var features_faza_sabz_8 = format_faza_sabz_8.readFeatures(json_faza_sabz_8, 
            {dataProjection: 'EPSG:4326', featureProjection: 'EPSG:3857'});
var jsonSource_faza_sabz_8 = new ol.source.Vector({
    attributions: ' ',
});
jsonSource_faza_sabz_8.addFeatures(features_faza_sabz_8);
var lyr_faza_sabz_8 = new ol.layer.Vector({
                declutter: true,
                source:jsonSource_faza_sabz_8,
maxResolution:1.4002233076130983,
 
                style: style_faza_sabz_8,
                interactive: true,
                title: '<img src="styles/legend/faza_sabz_8.png" /> faza_sabz'
            });
var format_educational_9 = new ol.format.GeoJSON();
var features_educational_9 = format_educational_9.readFeatures(json_educational_9, 
            {dataProjection: 'EPSG:4326', featureProjection: 'EPSG:3857'});
var jsonSource_educational_9 = new ol.source.Vector({
    attributions: ' ',
});
jsonSource_educational_9.addFeatures(features_educational_9);
var lyr_educational_9 = new ol.layer.Vector({
                declutter: true,
                source:jsonSource_educational_9,
maxResolution:1.4002233076130983,
 
                style: style_educational_9,
                interactive: true,
    title: 'educational<br />\
    <img src="styles/legend/educational_9_0.png" /> o<br />\
    <img src="styles/legend/educational_9_1.png" /> p<br />\
    <img src="styles/legend/educational_9_2.png" /> q<br />\
    <img src="styles/legend/educational_9_3.png" /> r<br />'
        });
var format_tejari_10 = new ol.format.GeoJSON();
var features_tejari_10 = format_tejari_10.readFeatures(json_tejari_10, 
            {dataProjection: 'EPSG:4326', featureProjection: 'EPSG:3857'});
var jsonSource_tejari_10 = new ol.source.Vector({
    attributions: ' ',
});
jsonSource_tejari_10.addFeatures(features_tejari_10);
var lyr_tejari_10 = new ol.layer.Vector({
                declutter: true,
                source:jsonSource_tejari_10,
maxResolution:1.4002233076130983,
 
                style: style_tejari_10,
                interactive: true,
    title: 'tejari<br />\
    <img src="styles/legend/tejari_10_0.png" /> h<br />\
    <img src="styles/legend/tejari_10_1.png" /> i<br />\
    <img src="styles/legend/tejari_10_2.png" /> j<br />\
    <img src="styles/legend/tejari_10_3.png" /> k<br />'
        });
var format_maskoni_11 = new ol.format.GeoJSON();
var features_maskoni_11 = format_maskoni_11.readFeatures(json_maskoni_11, 
            {dataProjection: 'EPSG:4326', featureProjection: 'EPSG:3857'});
var jsonSource_maskoni_11 = new ol.source.Vector({
    attributions: ' ',
});
jsonSource_maskoni_11.addFeatures(features_maskoni_11);
var lyr_maskoni_11 = new ol.layer.Vector({
                declutter: true,
                source:jsonSource_maskoni_11,
maxResolution:1.4002233076130983,
 
                style: style_maskoni_11,
                interactive: true,
    title: 'maskoni<br />\
    <img src="styles/legend/maskoni_11_0.png" /> a<br />\
    <img src="styles/legend/maskoni_11_1.png" /> b<br />\
    <img src="styles/legend/maskoni_11_2.png" /> c<br />\
    <img src="styles/legend/maskoni_11_3.png" /> d<br />'
        });
var format_flag_12 = new ol.format.GeoJSON();
var features_flag_12 = format_flag_12.readFeatures(json_flag_12, 
            {dataProjection: 'EPSG:4326', featureProjection: 'EPSG:3857'});
var jsonSource_flag_12 = new ol.source.Vector({
    attributions: ' ',
});
jsonSource_flag_12.addFeatures(features_flag_12);
var lyr_flag_12 = new ol.layer.Vector({
                declutter: true,
                source:jsonSource_flag_12,
maxResolution:28004.466152261964,
 minResolution:2.8004466152261966,

                style: style_flag_12,
                interactive: true,
                title: '<img src="styles/legend/flag_12.png" /> flag'
            });
var format_border_13 = new ol.format.GeoJSON();
var features_border_13 = format_border_13.readFeatures(json_border_13, 
            {dataProjection: 'EPSG:4326', featureProjection: 'EPSG:3857'});
var jsonSource_border_13 = new ol.source.Vector({
    attributions: ' ',
});
jsonSource_border_13.addFeatures(features_border_13);
var lyr_border_13 = new ol.layer.Vector({
                declutter: true,
                source:jsonSource_border_13,
maxResolution:28004.466152261964,
 minResolution:1.4002233076130983,

                style: style_border_13,
                interactive: true,
                title: '<img src="styles/legend/border_13.png" /> border'
            });

lyr_OpenStreetMap_0.setVisible(true);lyr_behdashtii_1.setVisible(true);lyr_edari_2.setVisible(true);lyr_gardashgari_3.setVisible(true);lyr_namayeshgaha_4.setVisible(true);lyr_mazhabi_5.setVisible(true);lyr_parking_6.setVisible(true);lyr_farhangi_7.setVisible(true);lyr_faza_sabz_8.setVisible(true);lyr_educational_9.setVisible(true);lyr_tejari_10.setVisible(true);lyr_maskoni_11.setVisible(true);lyr_flag_12.setVisible(true);lyr_border_13.setVisible(true);
var layersList = [lyr_OpenStreetMap_0,lyr_behdashtii_1,lyr_edari_2,lyr_gardashgari_3,lyr_namayeshgaha_4,lyr_mazhabi_5,lyr_parking_6,lyr_farhangi_7,lyr_faza_sabz_8,lyr_educational_9,lyr_tejari_10,lyr_maskoni_11,lyr_flag_12,lyr_border_13];
lyr_behdashtii_1.set('fieldAliases', {'address': 'address', 'density': 'density', 'date': 'date', 'stability': 'stability', 'label': 'label', 'area': 'area', 'Region': 'Region', 'karbari': 'karbari', 'id': 'id', 'owner': 'owner', 'rgb': 'rgb', });
lyr_edari_2.set('fieldAliases', {'density': 'density', 'date': 'date', 'stability': 'stability', 'label': 'label', 'Region': 'Region', 'area': 'area', 'karbari': 'karbari', 'id': 'id', 'address': 'address', 'owner': 'owner', 'rgb': 'rgb', });
lyr_gardashgari_3.set('fieldAliases', {'density': 'density', 'date': 'date', 'stability': 'stability', 'label': 'label', 'region': 'region', 'area': 'area', 'karbari': 'karbari', 'id': 'id', 'address': 'address', 'owner': 'owner', 'rgb': 'rgb', });
lyr_namayeshgaha_4.set('fieldAliases', {'address': 'address', 'density': 'density', 'date': 'date', 'stability': 'stability', 'label': 'label', 'Region': 'Region', 'area': 'area', 'karbari': 'karbari', 'id': 'id', 'owner': 'owner', 'rgb': 'rgb', });
lyr_mazhabi_5.set('fieldAliases', {'density': 'density', 'date': 'date', 'stability': 'stability', 'label': 'label', 'Region': 'Region', 'area': 'area', 'karbari': 'karbari', 'id': 'id', 'address': 'address', 'owner': 'owner', 'rgb': 'rgb', });
lyr_parking_6.set('fieldAliases', {'density': 'density', 'date': 'date', 'stability': 'stability', 'label': 'label', 'Region': 'Region', 'area': 'area', 'karbari': 'karbari', 'id': 'id', 'adress': 'adress', 'owner': 'owner', 'rgb': 'rgb', });
lyr_farhangi_7.set('fieldAliases', {'density': 'density', 'date': 'date', 'stability': 'stability', 'label': 'label', 'Region': 'Region', 'area': 'area', 'karbari': 'karbari', 'id': 'id', 'address': 'address', 'owner': 'owner', 'rgb': 'rgb', });
lyr_faza_sabz_8.set('fieldAliases', {'density': 'density', 'date': 'date', 'stability': 'stability', 'Region': 'Region', 'area': 'area', 'id': 'id', 'address': 'address', 'owner': 'owner', 'karbari': 'karbari', 'rgb': 'rgb', });
lyr_educational_9.set('fieldAliases', {'density': 'density', 'date': 'date', 'rgb': 'rgb', 'area': 'area', 'karbari': 'karbari', 'Region': 'Region', 'stability': 'stability', 'id': 'id', 'address': 'address', 'owner': 'owner', });
lyr_tejari_10.set('fieldAliases', {'density': 'density', 'date': 'date', 'rgb': 'rgb', 'area': 'area', 'karbari': 'karbari', 'stability': 'stability', 'Region': 'Region', 'id': 'id', 'address': 'address', 'owner': 'owner', });
lyr_maskoni_11.set('fieldAliases', {'density': 'density', 'rgb': 'rgb', 'date': 'date', 'karbari': 'karbari', 'area': 'area', 'Region': 'Region', 'stability': 'stability', 'id': 'id', 'address': 'address', 'owner': 'owner', });
lyr_flag_12.set('fieldAliases', {'name abadi': 'name abadi', 'Located at': 'Located at', 'Release da': 'Release da', 'Total land': 'Total land', 'number of': 'number of', 'sold build': 'sold build', 'not sold b': 'not sold b', 'lock build': 'lock build', 'resold bui': 'resold bui', 'not priced': 'not priced', 'building t': 'building t', 'building_1': 'building_1', 'least pric': 'least pric', 'most price': 'most price', 'number o_1': 'number o_1', 'all of the': 'all of the', 'number o_2': 'number o_2', 'sold bui_1': 'sold bui_1', 'not sold_1': 'not sold_1', 'lock bui_1': 'lock bui_1', 'resold b_1': 'resold b_1', 'not pric_1': 'not pric_1', 'building_2': 'building_2', 'building_3': 'building_3', 'least pr_1': 'least pr_1', 'most pri_1': 'most pri_1', 'number o_3': 'number o_3', 'all of t_1': 'all of t_1', 'number o_4': 'number o_4', 'sold bui_2': 'sold bui_2', 'not sold_2': 'not sold_2', 'lock bui_2': 'lock bui_2', 'resold b_2': 'resold b_2', 'not pric_2': 'not pric_2', 'building_4': 'building_4', 'building_5': 'building_5', 'least pr_2': 'least pr_2', 'most pri_2': 'most pri_2', 'number o_5': 'number o_5', 'all of t_2': 'all of t_2', 'number o_6': 'number o_6', 'number o_7': 'number o_7', 'number o_8': 'number o_8', 'number o_9': 'number o_9', 'number o10': 'number o10', 'number o11': 'number o11', 'number o12': 'number o12', });
lyr_border_13.set('fieldAliases', {'FID': 'FID', 'area': 'area', });
lyr_behdashtii_1.set('fieldImages', {'address': 'TextEdit', 'density': 'TextEdit', 'date': 'DateTime', 'stability': 'TextEdit', 'label': 'TextEdit', 'area': 'Range', 'Region': 'Range', 'karbari': 'TextEdit', 'id': 'TextEdit', 'owner': '', 'rgb': '', });
lyr_edari_2.set('fieldImages', {'density': 'TextEdit', 'date': 'DateTime', 'stability': 'TextEdit', 'label': 'TextEdit', 'Region': 'Range', 'area': 'Range', 'karbari': 'TextEdit', 'id': 'TextEdit', 'address': 'TextEdit', 'owner': '', 'rgb': '', });
lyr_gardashgari_3.set('fieldImages', {'density': 'TextEdit', 'date': 'DateTime', 'stability': 'TextEdit', 'label': 'TextEdit', 'region': 'Range', 'area': 'Range', 'karbari': 'TextEdit', 'id': 'TextEdit', 'address': 'TextEdit', 'owner': '', 'rgb': '', });
lyr_namayeshgaha_4.set('fieldImages', {'address': 'TextEdit', 'density': 'TextEdit', 'date': 'DateTime', 'stability': 'TextEdit', 'label': 'TextEdit', 'Region': 'Range', 'area': 'Range', 'karbari': 'TextEdit', 'id': 'TextEdit', 'owner': '', 'rgb': '', });
lyr_mazhabi_5.set('fieldImages', {'density': 'TextEdit', 'date': 'DateTime', 'stability': 'TextEdit', 'label': 'TextEdit', 'Region': 'Range', 'area': 'Range', 'karbari': 'TextEdit', 'id': 'TextEdit', 'address': 'TextEdit', 'owner': '', 'rgb': '', });
lyr_parking_6.set('fieldImages', {'density': 'TextEdit', 'date': 'DateTime', 'stability': 'TextEdit', 'label': 'TextEdit', 'Region': 'Range', 'area': 'Range', 'karbari': 'TextEdit', 'id': 'TextEdit', 'adress': 'TextEdit', 'owner': '', 'rgb': '', });
lyr_farhangi_7.set('fieldImages', {'density': 'TextEdit', 'date': 'DateTime', 'stability': 'TextEdit', 'label': 'TextEdit', 'Region': 'Range', 'area': 'Range', 'karbari': 'TextEdit', 'id': 'TextEdit', 'address': 'TextEdit', 'owner': '', 'rgb': '', });
lyr_faza_sabz_8.set('fieldImages', {'density': 'TextEdit', 'date': 'DateTime', 'stability': 'TextEdit', 'Region': 'Range', 'area': 'Range', 'id': 'TextEdit', 'address': 'TextEdit', 'owner': '', 'karbari': '', 'rgb': '', });
lyr_educational_9.set('fieldImages', {'density': 'TextEdit', 'date': 'DateTime', 'rgb': 'TextEdit', 'area': 'Range', 'karbari': 'TextEdit', 'Region': 'TextEdit', 'stability': 'Range', 'id': 'TextEdit', 'address': 'TextEdit', 'owner': '', });
lyr_tejari_10.set('fieldImages', {'density': 'TextEdit', 'date': 'DateTime', 'rgb': 'TextEdit', 'area': 'Range', 'karbari': 'TextEdit', 'stability': 'Range', 'Region': 'Range', 'id': 'TextEdit', 'address': 'TextEdit', 'owner': '', });
lyr_maskoni_11.set('fieldImages', {'density': 'TextEdit', 'rgb': 'TextEdit', 'date': 'DateTime', 'karbari': 'TextEdit', 'area': 'Range', 'Region': 'Range', 'stability': 'Range', 'id': 'TextEdit', 'address': 'TextEdit', 'owner': '', });
lyr_flag_12.set('fieldImages', {'name abadi': 'TextEdit', 'Located at': 'TextEdit', 'Release da': 'DateTime', 'Total land': 'TextEdit', 'number of': 'TextEdit', 'sold build': 'TextEdit', 'not sold b': 'TextEdit', 'lock build': 'TextEdit', 'resold bui': 'TextEdit', 'not priced': 'TextEdit', 'building t': 'TextEdit', 'building_1': 'TextEdit', 'least pric': 'TextEdit', 'most price': 'TextEdit', 'number o_1': 'TextEdit', 'all of the': 'TextEdit', 'number o_2': 'TextEdit', 'sold bui_1': 'TextEdit', 'not sold_1': 'TextEdit', 'lock bui_1': 'TextEdit', 'resold b_1': 'TextEdit', 'not pric_1': 'TextEdit', 'building_2': 'TextEdit', 'building_3': 'TextEdit', 'least pr_1': 'TextEdit', 'most pri_1': 'TextEdit', 'number o_3': 'TextEdit', 'all of t_1': 'TextEdit', 'number o_4': 'TextEdit', 'sold bui_2': 'TextEdit', 'not sold_2': 'TextEdit', 'lock bui_2': 'TextEdit', 'resold b_2': 'TextEdit', 'not pric_2': 'TextEdit', 'building_4': 'TextEdit', 'building_5': 'TextEdit', 'least pr_2': 'TextEdit', 'most pri_2': 'TextEdit', 'number o_5': 'TextEdit', 'all of t_2': 'TextEdit', 'number o_6': 'TextEdit', 'number o_7': 'TextEdit', 'number o_8': 'TextEdit', 'number o_9': 'TextEdit', 'number o10': 'TextEdit', 'number o11': 'TextEdit', 'number o12': 'TextEdit', });
lyr_border_13.set('fieldImages', {'FID': 'TextEdit', 'area': '', });
lyr_behdashtii_1.set('fieldLabels', {'address': 'inline label', 'density': 'inline label', 'date': 'inline label', 'stability': 'inline label', 'label': 'inline label', 'area': 'inline label', 'Region': 'inline label', 'karbari': 'inline label', 'id': 'inline label', 'owner': 'inline label', 'rgb': 'inline label', });
lyr_edari_2.set('fieldLabels', {'density': 'inline label', 'date': 'inline label', 'stability': 'inline label', 'label': 'inline label', 'Region': 'inline label', 'area': 'inline label', 'karbari': 'inline label', 'id': 'inline label', 'address': 'inline label', 'owner': 'inline label', 'rgb': 'inline label', });
lyr_gardashgari_3.set('fieldLabels', {'density': 'inline label', 'date': 'inline label', 'stability': 'inline label', 'label': 'inline label', 'region': 'inline label', 'area': 'inline label', 'karbari': 'inline label', 'id': 'inline label', 'address': 'inline label', 'owner': 'inline label', 'rgb': 'inline label', });
lyr_namayeshgaha_4.set('fieldLabels', {'address': 'inline label', 'density': 'inline label', 'date': 'inline label', 'stability': 'inline label', 'label': 'inline label', 'Region': 'inline label', 'area': 'inline label', 'karbari': 'inline label', 'id': 'inline label', 'owner': 'inline label', 'rgb': 'inline label', });
lyr_mazhabi_5.set('fieldLabels', {'density': 'inline label', 'date': 'inline label', 'stability': 'inline label', 'label': 'inline label', 'Region': 'inline label', 'area': 'inline label', 'karbari': 'inline label', 'id': 'inline label', 'address': 'inline label', 'owner': 'inline label', 'rgb': 'inline label', });
lyr_parking_6.set('fieldLabels', {'density': 'inline label', 'date': 'inline label', 'stability': 'inline label', 'label': 'inline label', 'Region': 'inline label', 'area': 'inline label', 'karbari': 'inline label', 'id': 'inline label', 'adress': 'inline label', 'owner': 'inline label', 'rgb': 'inline label', });
lyr_farhangi_7.set('fieldLabels', {'density': 'inline label', 'date': 'inline label', 'stability': 'inline label', 'label': 'inline label', 'Region': 'inline label', 'area': 'inline label', 'karbari': 'inline label', 'id': 'inline label', 'address': 'inline label', 'owner': 'inline label', 'rgb': 'inline label', });
lyr_faza_sabz_8.set('fieldLabels', {'density': 'inline label', 'date': 'inline label', 'stability': 'inline label', 'Region': 'inline label', 'area': 'inline label', 'id': 'inline label', 'address': 'inline label', 'owner': 'inline label', 'karbari': 'inline label', 'rgb': 'inline label', });
lyr_educational_9.set('fieldLabels', {'density': 'inline label', 'date': 'inline label', 'rgb': 'inline label', 'area': 'inline label', 'karbari': 'inline label', 'Region': 'inline label', 'stability': 'inline label', 'id': 'inline label', 'address': 'inline label', 'owner': 'inline label', });
lyr_tejari_10.set('fieldLabels', {'density': 'inline label', 'date': 'inline label', 'rgb': 'inline label', 'area': 'inline label', 'karbari': 'inline label', 'stability': 'inline label', 'Region': 'inline label', 'id': 'inline label', 'address': 'inline label', 'owner': 'inline label', });
lyr_maskoni_11.set('fieldLabels', {'density': 'inline label', 'rgb': 'inline label', 'date': 'inline label', 'karbari': 'inline label', 'area': 'inline label', 'Region': 'inline label', 'stability': 'inline label', 'id': 'inline label', 'address': 'inline label', 'owner': 'inline label', });
lyr_flag_12.set('fieldLabels', {'name abadi': 'inline label', 'Located at': 'inline label', 'Release da': 'inline label', 'Total land': 'inline label', 'number of': 'inline label', 'sold build': 'inline label', 'not sold b': 'inline label', 'lock build': 'inline label', 'resold bui': 'inline label', 'not priced': 'inline label', 'building t': 'inline label', 'building_1': 'inline label', 'least pric': 'inline label', 'most price': 'inline label', 'number o_1': 'inline label', 'all of the': 'inline label', 'number o_2': 'inline label', 'sold bui_1': 'inline label', 'not sold_1': 'inline label', 'lock bui_1': 'inline label', 'resold b_1': 'inline label', 'not pric_1': 'inline label', 'building_2': 'inline label', 'building_3': 'inline label', 'least pr_1': 'inline label', 'most pri_1': 'inline label', 'number o_3': 'inline label', 'all of t_1': 'inline label', 'number o_4': 'inline label', 'sold bui_2': 'inline label', 'not sold_2': 'inline label', 'lock bui_2': 'inline label', 'resold b_2': 'inline label', 'not pric_2': 'inline label', 'building_4': 'inline label', 'building_5': 'inline label', 'least pr_2': 'inline label', 'most pri_2': 'inline label', 'number o_5': 'inline label', 'all of t_2': 'inline label', 'number o_6': 'inline label', 'number o_7': 'inline label', 'number o_8': 'inline label', 'number o_9': 'inline label', 'number o10': 'inline label', 'number o11': 'inline label', 'number o12': 'inline label', });
lyr_border_13.set('fieldLabels', {'FID': 'inline label', 'area': 'no label', });
lyr_border_13.on('precompose', function(evt) {
    evt.context.globalCompositeOperation = 'normal';
});