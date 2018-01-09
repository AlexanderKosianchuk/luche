import './vertical-toolbar.sass';

import React, { Component } from 'react';
import { connect } from 'react-redux';
import { bindActionCreators } from 'redux';
import { Translate, I18n } from 'react-redux-i18n';
import uuidV4 from 'uuid/v4';

import FdrSelector from 'controls/fdr-selector/FdrSelector';
import CalibrationSelector from 'controls/calibration-selector/CalibrationSelector';

import interactionRequest from 'actions/particular/interactionRequest';
import transmit from 'actions/transmit';
import request from 'actions/request';

const TOP_MENU_HEIGHT = 51;
const MIN_WINDOW_WIDTH = 768; // check is mobile

class VerticalToolbar extends Component {
    static form = null;

    constructor(props) {
        super(props);

        this.state = {
            isRunning: false,
            fakeData: false,
            sources: ['192.168.1.2']
        }

        // this handler will be filled by FdrSelector
        // by method that allows to get selected FDR ID
        this.handler = {
            getSelectedFdrId: null,
            getSelectedCalibrationId: null,
        }
    }

    componentDidMount() {
        if (window.innerWidth > MIN_WINDOW_WIDTH) {
            this.form.style.height = (window.innerHeight - TOP_MENU_HEIGHT) + 'px';
        }
    }

    buildIpsInputs() {
        let ips = [];

        for (let ii = 0; ii < this.state.sources.length; ii++) {
            ips.push(<input
                key={ ii }
                className='form-control'
                name='ip[]'
                value={ this.state.sources[ii] }
                onChange={ this.handleChange.bind(this, ii) }
                />
            );
        }

        return ips;
    }

    handleChange(index, event) {
        let sources = this.state.sources.slice();
        sources[index] = event.target.value;

        this.setState({ sources: sources });
    }

    handleAddSourceClick(event) {
        event.preventDefault();

        let sources = this.state.sources.slice();
        sources.push('');

        this.setState({ sources: sources });
    }

    handleStartClick(event) {
        event.preventDefault();

        let fdrId = null;
        let calibrationId = null;

        if (typeof this.handler.getSelectedFdrId === 'function') {
            fdrId = this.handler.getSelectedFdrId();
        }

        if (typeof this.handler.getSelectedCalibrationId === 'function') {
            calibrationId = this.handler.getSelectedCalibrationId();
        }

        let ipInputs = this.form.querySelectorAll('input[name="ip[]"]');
        let ips = [];

        ipInputs.forEach((item) => {
            if (item.value.length >= 7) {
                ips.push(item.value);
            }
        });

        if (ips.length === 0) {
            alert(I18n.t('realtimeCalibration.verticalToolbar.enterIpToConnect'));
            return;
        }

        var data = new FormData();
        data.append('uid', uuidV4().substring(0, 18).replace(/-/g, ''));
        data.append('fdrId', fdrId);
        data.append('calibrationId', calibrationId);
        data.append('ips', ips);
        data.append('fakeData', this.state.fakeData);
        data.append('cors', window.location.hostname);

        this.props.request(
            ['interaction', 'up'],
            'get'
        ).then((resp) => {
            return this.props.interactionRequest(
                this.props.interactionUrl,
                '/realtimeCalibration/initConnection',
                data
            );
        }).then(() => {
            this.props.transmit(
                'CHANGE_REALTIME_CALIBRATING_STATUS',
                { status: 'init' }
            );
        }).then(() => {
            return this.props.interactionRequest(
                this.props.interactionUrl,
                '/realtimeCalibration/startUdp',
                data
            );
        });
    }

    handleFakeDataClick() {
        this.setState({ fakeData: !this.state.fakeData });
    }

    render() {
        return (
            <form
                className='realtime-calibration-vertical-toolbar'
                ref={ (form) => { this.form = form; } }
            >
                <div className='realtime-calibration-vertical-toolbar__connection-params'>
                    <Translate value='realtimeCalibration.verticalToolbar.connectionParams'/>
                </div>
                <div className='realtime-calibration-vertical-toolbar__label'>
                    <Translate value='realtimeCalibration.verticalToolbar.fdrType'/>
                </div>
                <div>
                    <ul className='realtime-calibration-vertical-toolbar__fdr-type'>
                        <FdrSelector
                            methodHandler={ this.handler }
                        />
                        <CalibrationSelector
                            methodHandler={ this.handler }
                        />
                    </ul>
                </div>
                <div className='realtime-calibration-vertical-toolbar__label'>
                    <Translate value='realtimeCalibration.verticalToolbar.connectionType'/>
                </div>
                <div className='realtime-calibration-vertical-toolbar__controll'>
                    <select className='form-control' name='connectionType'>
                        <option value='udp'>UDP</option>
                    </select>
                </div>
                <div className='realtime-calibration-vertical-toolbar__label'>
                    <Translate value='realtimeCalibration.verticalToolbar.sourceIps'/>
                </div>
                <div className='realtime-calibration-vertical-toolbar__controll'>
                    { this.buildIpsInputs() }
                </div>
                <div className='realtime-calibration-vertical-toolbar__button'>
                    <button
                        className='btn btn-default'
                        onClick={ this.handleAddSourceClick.bind(this) }
                    >
                        <Translate value='realtimeCalibration.verticalToolbar.addSource'/>
                    </button>
                </div>
                <div className='realtime-calibration-vertical-toolbar__inline-label-container'>
                    <div className='realtime-calibration-vertical-toolbar__inline-label'>
                        <Translate value='realtimeCalibration.verticalToolbar.fakeData'/>
                    </div>
                    <div className='realtime-calibration-vertical-toolbar__inline-label'>
                        <input className='form-control realtime-calibration-vertical-toolbar__checkbox' type='checkbox'
                            onClick={ this.handleFakeDataClick.bind(this) }
                            checked={ (this.state.fakeData === true) ? 'checked' : '' }
                        />
                    </div>
                </div>
                <div className='realtime-calibration-vertical-toolbar__button'>
                    { this.state.isRunning ? (
                        <button
                          className='btn btn-default'
                          onClick={ this.handleStopClick.bind(this) }
                        >
                            <Translate value='realtimeCalibration.verticalToolbar.stop'/>
                        </button>
                    ) : (
                        <button
                          className='btn btn-default'
                          onClick={ this.handleStartClick.bind(this) }
                        >
                            <Translate value='realtimeCalibration.verticalToolbar.start'/>
                        </button>
                    )}
                </div>
            </form>
        );
    }
}

function mapStateToProps(state) {
    return {};
}

function mapDispatchToProps(dispatch) {
    return {
        interactionRequest: bindActionCreators(interactionRequest, dispatch),
        transmit: bindActionCreators(transmit, dispatch),
        request: bindActionCreators(request, dispatch)
    }
}

export default connect(mapStateToProps, mapDispatchToProps)(VerticalToolbar);