import React from 'react';
import { connect } from 'react-redux';

import MainPage from 'controls/main-page/MainPage';
import FlightTemplateEditToolbar from 'controls/flight-template-edit-toolbar/FlightTemplateEditToolbar';
import Params from 'components/flight-template-update/params/Params';

class FlightTemplateUpdate extends React.Component {
    render () {
        return (
            <div>
                <MainPage/>
                <FlightTemplateEditToolbar
                    flightId={ this.props.flightId }
                    templateName={ this.props.templateName }
                />
                <Params
                    flightId={ this.props.flightId }
                    templateName={ this.props.templateName }
                    colorPickerEnabled={ false }
                />
            </div>
        );
    }
}

function mapStateToProps(state, ownProps) {
    return {
        flightId: ownProps.match.params.flightId,
        templateName: ownProps.match.params.templateName
    };
}

export default connect(mapStateToProps, () => { return {} })(FlightTemplateUpdate);