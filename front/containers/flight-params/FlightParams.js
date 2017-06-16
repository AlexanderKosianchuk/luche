import React from 'react';
import { connect } from 'react-redux';
import { bindActionCreators } from 'redux';

import MainPage from 'controls/main-page/MainPage';
import Toolbar from 'components/flight-params/toolbar/Toolbar';
import CycloParams from 'controls/cyclo-params/CycloParams';

import showPage from 'actions/showPage';

class FlightParams extends React.Component {
    render () {
        return (
            <div>
                <MainPage/>
                <Toolbar flightId={ this.props.flightId }/>
                <CycloParams flightId={ this.props.flightId }/>
            </div>
        );
    }
}

function mapStateToProps(state, ownProps) {
    return {
        flightId: ownProps.match.params.id
    };
}

function mapDispatchToProps(dispatch) {
    return {
        showPage: bindActionCreators(showPage, dispatch)
    }
}

export default connect(mapStateToProps, mapDispatchToProps)(FlightParams);