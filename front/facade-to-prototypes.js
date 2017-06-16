/*jslint browser: true*/
/*global $, jQuery*/
/*global Language, WindowFactory, FlightList, FlightUploader*/
/*global FlightEvents, Fdr, Chart, User, SearchFlight*/

'use strict';

// libs
import 'jquery';
import 'jquery-ui';
import 'jquery-ui/ui/widgets/button';
import 'jquery-ui/ui/widgets/menu';

import 'jstree';
import 'datatables';
import 'bootstrap-loader';

// lib styles
import 'jquery-ui/themes/base/all.css';
import 'jstree/dist/themes/default/style.min.css';

//old styles
import 'stylesheets/style.css';

// old prototypes
import FlightList from 'FlightList';
import FlightUploader from 'FlightUploader';
import FlightEvents from 'FlightEvents';
import ChartService from 'Chart';
import User from 'User';
import SearchFlight from 'SearchFlight';
import Calibration from 'Calibration';

import { push } from 'react-router-redux'
import startFlightUploading from 'actions/startFlightUploading';

export default function facade(store) {
    function refreshFlightsList() {
        function getCurrentRoute(state) {
            return state.router.location.pathname;
        }

        let currentLocation = getCurrentRoute(store.getState())

        if ((currentLocation === '/')
            || (currentLocation === '/#')
            || (currentLocation.indexOf('flights/tree') > -1)
        ) {
            $(document).trigger('flightsTreeShow', [
                $('#container')
            ]);
        }

        if (currentLocation.indexOf('flights/table') > -1) {
            $(document).trigger('flightsTableShow', [
                $('#container')
            ]);
        }
    }

    let currentFlightUploadingStateValue;
    store.subscribe(() => {
        function getUploadingState(state) {
            return state.flightUploadingState.length;
        }

        let previousFlightUploadingStateValue = currentFlightUploadingStateValue;
         currentFlightUploadingStateValue = getUploadingState(store.getState())

        if ((currentFlightUploadingStateValue === 0)
            && (previousFlightUploadingStateValue > 0)
        ) {
            refreshFlightsList();
        }
    });

    $(document).on('importItem', function (e, form) {
        let dfd = $.Deferred();
        let FU = new FlightUploader(store);
        FU.Import(form, dfd);
        dfd.promise();

        dfd.then(() => {
            if ($('#container')) {
                refreshFlightsList();
            }
        });
    });

    $(document).on('uploadWithPreview', function (e, showcase, uploadingUid, fdrId, calibrationId) {
        let FU = new FlightUploader(store);
        FU.FillFactoryContaider(showcase, uploadingUid, fdrId, calibrationId);
    });

    $(document).on('startProccessing', function (e, uploadingUid) {
        store.dispatch(startFlightUploading({
            uploadingUid: uploadingUid
        }));
    });

    $(document).on('endProccessing', function (e, uploadingUid) {
        store.dispatch(() => () => {
            dispatch({
                type: 'FLIGHT_UPLOADING_COMPLETE',
                payload: {
                    uploadingUid: uploadingUid
                }
            });
        });
    });

    $(document).on('flightsTreeShow', function (e, someshowcase) {
        let FL = new FlightList(store);
        FL.FillFactoryContaider(someshowcase);
    });

    $(document).on('flightsTableShow', function (e, someshowcase) {
        let FL = new FlightList(store);
        FL.setView('table');
        FL.FillFactoryContaider(someshowcase);
    });

    $(document).on('flightEvents', function (e, someshowcase, flightId) {
        let FO = new FlightEvents(store);
        FO.flightId = flightId;
        FO.FillFactoryContaider(someshowcase);
    });

    $(document).on('chartShow', function (
        e, showcase,
        flightId, tplName,
        stepLength, startCopyTime,
        startFrame, endFrame,
        apParams, bpParams
    ) {
        var C = new ChartService(store);
        C.SetChartData(
            flightId, tplName,
            stepLength, startCopyTime,
            startFrame, endFrame,
            apParams, bpParams
        );
        C.FillFactoryContaider(showcase);
    });

    $(document).on('userShowList', function (e, showcase) {
        let U = new User(store);
        U.FillFactoryContaider(showcase);
    });

    $(document).on('changeLanguage', function (e, newLanguage) {
        let U = new User(store);
        U.changeLanguage(newLanguage);
    });

    $(document).on('flightSearchFormShow', function (e, showcase) {
        let SF = new SearchFlight(store);
        SF.FillFactoryContaider(showcase);
    });

    $(document).on('calibrationsShow', function (e, showcase) {
        let CLB = new Calibration(store);
        CLB.FillFactoryContaider(showcase);
    });

    $(document).on('uploadPreviewedFlight', function(uploadingUid, fdrId, calibrationId) {
        let FU = new FlightUploader(store);
        FU.uploadPreviewed().then(() => {
            store.dispatch(push('/'));
        });
    });
}